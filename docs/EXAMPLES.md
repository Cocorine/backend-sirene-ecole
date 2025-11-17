# Exemples Concrets et Patterns Courants

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Patterns de Repository](#patterns-de-repository)
3. [Patterns de Service](#patterns-de-service)
4. [Relations Eloquent](#relations-eloquent)
5. [Validation avancée](#validation-avancée)
6. [Transactions et gestion d'erreurs](#transactions-et-gestion-derreurs)
7. [Pagination et filtres](#pagination-et-filtres)
8. [Upload de fichiers](#upload-de-fichiers)
9. [Envoi de notifications](#envoi-de-notifications)
10. [Middleware personnalisés](#middleware-personnalisés)

---

## Introduction

Ce document présente des **exemples concrets** tirés du projet Backend Sirene École pour illustrer les patterns et bonnes pratiques.

---

## Patterns de Repository

### Exemple 1 : Repository avec recherche complexe

**Contexte :** Rechercher des écoles avec filtres multiples

```php
<?php

namespace App\Repositories;

use App\Models\Ecole;
use App\Repositories\Contracts\EcoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EcoleRepository extends BaseRepository implements EcoleRepositoryInterface
{
    public function __construct(Ecole $model)
    {
        parent::__construct($model);
    }

    /**
     * Recherche avancée avec filtres multiples
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Filtre par nom
        if (!empty($filters['nom'])) {
            $query->where('nom', 'LIKE', "%{$filters['nom']}%");
        }

        // Filtre par ville
        if (!empty($filters['ville_id'])) {
            $query->where('ville_id', $filters['ville_id']);
        }

        // Filtre par statut
        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        // Filtre par date de création
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Recherche par abonnement actif
        if (isset($filters['has_active_subscription']) && $filters['has_active_subscription']) {
            $query->whereHas('abonnements', function ($q) {
                $q->where('statut', StatutAbonnement::ACTIF)
                  ->where('date_fin', '>', now());
            });
        }

        // Tri
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Eager loading
        $query->with(['ville', 'abonnements']);

        // Pagination
        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Récupérer les écoles avec abonnement expirant bientôt
     */
    public function getEcolesAbonnementExpirantSoon(int $days = 30): Collection
    {
        return $this->model
            ->whereHas('abonnements', function ($query) use ($days) {
                $query->where('statut', StatutAbonnement::ACTIF)
                    ->whereBetween('date_fin', [
                        now(),
                        now()->addDays($days)
                    ]);
            })
            ->with(['abonnements' => function ($query) {
                $query->where('statut', StatutAbonnement::ACTIF)
                    ->latest();
            }])
            ->get();
    }
}
```

**Utilisation dans le Controller :**

```php
public function index(Request $request): JsonResponse
{
    $ecoles = $this->ecoleService->search($request->query());

    return $this->successResponse(
        EcoleResource::collection($ecoles),
        'Écoles récupérées avec succès'
    );
}
```

---

### Exemple 2 : Repository avec méthodes utilitaires

```php
<?php

namespace App\Repositories;

class SireneRepository extends BaseRepository implements SireneRepositoryInterface
{
    /**
     * Vérifier la disponibilité d'un numéro de série
     */
    public function isNumeroSerieAvailable(string $numeroSerie, ?string $excludeId = null): bool
    {
        $query = $this->model->where('numero_serie', $numeroSerie);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Récupérer les sirènes nécessitant une maintenance
     */
    public function getSirenesNeedingMaintenance(): Collection
    {
        return $this->model
            ->where('derniere_maintenance', '<', now()->subMonths(6))
            ->orWhereNull('derniere_maintenance')
            ->where('statut', StatutSirene::ACTIVE)
            ->with('ecole', 'modele')
            ->get();
    }

    /**
     * Compter les sirènes par statut
     */
    public function countByStatut(): array
    {
        return $this->model
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();
    }

    /**
     * Récupérer les sirènes d'une école avec leurs pannes
     */
    public function getSirenesWithPannesByEcole(string $ecoleId): Collection
    {
        return $this->model
            ->where('ecole_id', $ecoleId)
            ->with(['pannes' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->get();
    }
}
```

---

## Patterns de Service

### Exemple 1 : Service avec logique métier complexe

**Contexte :** Créer un abonnement avec paiement

```php
<?php

namespace App\Services;

use App\Enums\StatutAbonnement;
use App\Enums\StatutPaiement;
use App\Models\Abonnement;
use App\Notifications\PaymentValidatedNotification;
use App\Repositories\Contracts\AbonnementRepositoryInterface;
use App\Repositories\Contracts\PaiementRepositoryInterface;
use App\Services\Contracts\AbonnementServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AbonnementService implements AbonnementServiceInterface
{
    public function __construct(
        private AbonnementRepositoryInterface $abonnementRepository,
        private PaiementRepositoryInterface $paiementRepository,
    ) {}

    /**
     * Créer un abonnement pour une école
     */
    public function createForEcole(string $ecoleId, array $paiementData = []): Abonnement
    {
        DB::beginTransaction();

        try {
            // 1. Vérifier s'il existe un abonnement actif
            $abonnementActif = $this->abonnementRepository->getActiveByEcole($ecoleId);

            if ($abonnementActif) {
                throw new \Exception('Cette école a déjà un abonnement actif.');
            }

            // 2. Créer l'abonnement
            $dateDebut = now();
            $dateFin = now()->addYear();
            $montant = config('abonnement.price_per_year');

            $abonnement = $this->abonnementRepository->create([
                'ecole_id' => $ecoleId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'montant' => $montant,
                'statut' => StatutAbonnement::EN_ATTENTE,
            ]);

            // 3. Générer le numéro d'abonnement
            $abonnement->generateNumeroAbonnement();

            // 4. Générer le QR Code
            $abonnement->generateQrCode();

            // 5. Créer le paiement si les données sont fournies
            if (!empty($paiementData)) {
                $paiement = $this->paiementRepository->create([
                    'abonnement_id' => $abonnement->id,
                    'montant' => $montant,
                    'statut' => StatutPaiement::PENDING,
                    'moyen_paiement' => $paiementData['moyen_paiement'] ?? null,
                    'reference' => $paiementData['reference'] ?? null,
                ]);

                // Valider automatiquement si paiement confirmé
                if ($paiementData['validated'] ?? false) {
                    $this->validatePaiement($paiement->id);
                }
            }

            DB::commit();

            return $abonnement->fresh(['ecole', 'paiements']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Renouveler un abonnement
     */
    public function renouveler(string $abonnementId): Abonnement
    {
        DB::beginTransaction();

        try {
            $ancienAbonnement = $this->abonnementRepository->find($abonnementId);

            if (!$ancienAbonnement) {
                throw new \Exception('Abonnement non trouvé.');
            }

            // Expirer l'ancien abonnement
            $this->abonnementRepository->update($ancienAbonnement->id, [
                'statut' => StatutAbonnement::EXPIRE
            ]);

            // Créer le nouvel abonnement
            $dateDebut = $ancienAbonnement->date_fin->addDay();
            $dateFin = $dateDebut->copy()->addYear();

            $nouvelAbonnement = $this->abonnementRepository->create([
                'ecole_id' => $ancienAbonnement->ecole_id,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'montant' => config('abonnement.price_per_year'),
                'statut' => StatutAbonnement::EN_ATTENTE,
            ]);

            $nouvelAbonnement->generateNumeroAbonnement();
            $nouvelAbonnement->generateQrCode();

            DB::commit();

            return $nouvelAbonnement->fresh('ecole');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Valider un paiement et activer l'abonnement
     */
    private function validatePaiement(string $paiementId): void
    {
        $paiement = $this->paiementRepository->find($paiementId);

        // Mettre à jour le paiement
        $this->paiementRepository->update($paiementId, [
            'statut' => StatutPaiement::VALIDATED,
            'date_paiement' => now(),
        ]);

        // Activer l'abonnement
        $this->abonnementRepository->update($paiement->abonnement_id, [
            'statut' => StatutAbonnement::ACTIF
        ]);

        // Envoyer une notification
        $abonnement = $paiement->abonnement;
        Notification::send(
            $abonnement->ecole,
            new PaymentValidatedNotification($abonnement)
        );
    }
}
```

---

### Exemple 2 : Service avec orchestration multi-repositories

**Contexte :** Créer une intervention technique

```php
<?php

namespace App\Services;

use App\Enums\StatutIntervention;
use App\Enums\StatutMission;
use App\Models\Intervention;
use App\Notifications\NewMissionOrderNotification;
use App\Repositories\Contracts\InterventionRepositoryInterface;
use App\Repositories\Contracts\OrdreMissionRepositoryInterface;
use App\Repositories\Contracts\PanneRepositoryInterface;
use App\Services\Contracts\InterventionServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InterventionService implements InterventionServiceInterface
{
    public function __construct(
        private InterventionRepositoryInterface $interventionRepository,
        private OrdreMissionRepositoryInterface $ordreMissionRepository,
        private PanneRepositoryInterface $panneRepository,
    ) {}

    /**
     * Créer une intervention à partir d'une panne
     */
    public function createFromPanne(string $panneId, array $data): Intervention
    {
        DB::beginTransaction();

        try {
            // 1. Récupérer la panne
            $panne = $this->panneRepository->find($panneId);

            if (!$panne) {
                throw new \Exception('Panne non trouvée.');
            }

            // 2. Créer l'ordre de mission
            $ordreMission = $this->ordreMissionRepository->create([
                'panne_id' => $panne->id,
                'sirene_id' => $panne->sirene_id,
                'ecole_id' => $panne->sirene->ecole_id,
                'description' => $data['description'] ?? "Intervention pour panne #{$panne->id}",
                'statut' => StatutMission::OPEN,
                'date_prevue' => $data['date_prevue'] ?? now()->addDay(),
            ]);

            // 3. Créer l'intervention
            $intervention = $this->interventionRepository->create([
                'ordre_mission_id' => $ordreMission->id,
                'technicien_id' => $data['technicien_id'],
                'statut' => StatutIntervention::EN_ATTENTE,
                'date_intervention' => $data['date_intervention'] ?? now(),
            ]);

            // 4. Assigner le technicien à la mission
            $this->assignTechnicien($ordreMission->id, $data['technicien_id']);

            // 5. Envoyer une notification au technicien
            $technicien = $intervention->technicien;
            Notification::send(
                $technicien,
                new NewMissionOrderNotification($ordreMission)
            );

            DB::commit();

            return $intervention->fresh([
                'ordreMission.panne.sirene.ecole',
                'technicien'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Marquer une intervention comme terminée
     */
    public function terminer(string $interventionId, array $rapportData): Intervention
    {
        DB::beginTransaction();

        try {
            // 1. Mettre à jour l'intervention
            $intervention = $this->interventionRepository->update($interventionId, [
                'statut' => StatutIntervention::TERMINEES,
                'date_fin' => now(),
            ]);

            // 2. Créer le rapport d'intervention
            $intervention->rapport()->create([
                'description' => $rapportData['description'],
                'travaux_effectues' => $rapportData['travaux_effectues'],
                'pieces_utilisees' => $rapportData['pieces_utilisees'] ?? null,
                'temps_passe' => $rapportData['temps_passe'] ?? null,
            ]);

            // 3. Clôturer l'ordre de mission
            $this->ordreMissionRepository->update($intervention->ordre_mission_id, [
                'statut' => StatutMission::CLOSED,
                'date_cloture' => now(),
            ]);

            // 4. Résoudre la panne
            $this->panneRepository->update($intervention->ordreMission->panne_id, [
                'statut' => StatutPanne::RESOLUE,
                'date_resolution' => now(),
            ]);

            DB::commit();

            return $intervention->fresh(['rapport', 'ordreMission']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assigner un technicien à une mission
     */
    private function assignTechnicien(string $ordreMissionId, string $technicienId): void
    {
        $this->ordreMissionRepository->assignTechnicien($ordreMissionId, $technicienId);
    }
}
```

---

## Relations Eloquent

### Exemple 1 : Relations complexes avec polymorphisme

**Contexte :** UserInfo peut être lié à Ecole ou Technicien

```php
<?php

namespace App\Models;

class UserInfo extends Model
{
    /**
     * Relation polymorphique
     */
    public function userable()
    {
        return $this->morphTo();
    }
}

class Ecole extends Model
{
    /**
     * Relation polymorphique inverse
     */
    public function userInfo()
    {
        return $this->morphOne(UserInfo::class, 'userable');
    }
}

class Technicien extends Model
{
    public function userInfo()
    {
        return $this->morphOne(UserInfo::class, 'userable');
    }
}
```

**Utilisation :**

```php
// Créer un UserInfo pour une école
$ecole = Ecole::find('01HQ...');
$ecole->userInfo()->create([
    'prenom' => 'Jean',
    'nom' => 'Dupont',
    'telephone' => '+33123456789',
]);

// Récupérer l'entité parente
$userInfo = UserInfo::find('01HQ...');
$parent = $userInfo->userable; // Retourne Ecole ou Technicien
```

---

### Exemple 2 : Relations Many-to-Many avec pivot

**Contexte :** Rôles et Permissions

```php
<?php

namespace App\Models;

class Role extends Model
{
    /**
     * Permissions du rôle (Many-to-Many)
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions', // Table pivot
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Assigner une permission
     */
    public function assignPermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching($permission);
    }

    /**
     * Retirer une permission
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission);
    }

    /**
     * Vérifier si le rôle a une permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->exists();
    }
}
```

**Utilisation :**

```php
$role = Role::find('01HQ...');

// Assigner des permissions
$role->permissions()->attach(['01HQ...', '01HR...']);

// Synchroniser (remplacer toutes les permissions)
$role->permissions()->sync(['01HQ...', '01HR...']);

// Vérifier une permission
if ($role->hasPermission('create-ecole')) {
    // Autoriser
}
```

---

## Validation avancée

### Exemple 1 : Validation conditionnelle

```php
<?php

namespace App\Http\Requests;

class CreatePaiementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'abonnement_id' => ['required', 'exists:abonnements,id'],
            'montant' => ['required', 'numeric', 'min:0'],
            'moyen_paiement' => ['required', Rule::enum(MoyenPaiement::class)],

            // Champs conditionnels selon le moyen de paiement
            'reference_transaction' => [
                'required_if:moyen_paiement,' . MoyenPaiement::CINETPAY->value,
                'string'
            ],

            'numero_cheque' => [
                'required_if:moyen_paiement,' . MoyenPaiement::CHEQUE->value,
                'string'
            ],

            'banque' => [
                'required_if:moyen_paiement,' . MoyenPaiement::CHEQUE->value,
                'string'
            ],
        ];
    }

    /**
     * Validation personnalisée
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que le montant correspond à l'abonnement
            $abonnement = Abonnement::find($this->abonnement_id);

            if ($abonnement && $this->montant != $abonnement->montant) {
                $validator->errors()->add(
                    'montant',
                    'Le montant ne correspond pas à l\'abonnement.'
                );
            }
        });
    }
}
```

---

### Exemple 2 : Règles de validation custom

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPhoneNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        // Validation du format téléphone ivoirien
        return preg_match('/^(\+225|00225|0)?[0-9]{10}$/', $value);
    }

    public function message(): string
    {
        return 'Le numéro de téléphone n\'est pas valide. Format attendu: +225XXXXXXXXXX';
    }
}
```

**Utilisation :**

```php
public function rules(): array
{
    return [
        'telephone' => ['required', new ValidPhoneNumber()],
    ];
}
```

---

## Transactions et gestion d'erreurs

### Exemple 1 : Transaction avec rollback automatique

```php
public function createEcoleWithSites(array $data): Ecole
{
    return DB::transaction(function () use ($data) {
        // 1. Créer l'école
        $ecole = $this->ecoleRepository->create([
            'nom' => $data['nom'],
            'email' => $data['email'],
            'ville_id' => $data['ville_id'],
        ]);

        // 2. Générer le code
        $ecole->generateCodeEtablissement();

        // 3. Créer les sites
        foreach ($data['sites'] as $siteData) {
            $ecole->sites()->create($siteData);
        }

        // 4. Créer l'abonnement initial
        $this->abonnementService->createForEcole($ecole->id);

        return $ecole->fresh(['sites', 'abonnements']);
    });
}
```

---

### Exemple 2 : Gestion d'erreurs métier

```php
public function declarerPanne(string $sireneId, array $data): Panne
{
    $sirene = $this->sireneRepository->find($sireneId);

    // Validations métier
    if (!$sirene) {
        throw new NotFoundException('Sirène non trouvée.');
    }

    if ($sirene->statut !== StatutSirene::ACTIVE) {
        throw new BusinessException(
            'Impossible de déclarer une panne sur une sirène inactive.'
        );
    }

    // Vérifier s'il existe déjà une panne non résolue
    $panneEnCours = $this->panneRepository->getPanneNonResolue($sireneId);

    if ($panneEnCours) {
        throw new BusinessException(
            'Une panne est déjà en cours de traitement pour cette sirène.',
            ['panne_id' => $panneEnCours->id]
        );
    }

    return DB::transaction(function () use ($sireneId, $data) {
        // Créer la panne
        $panne = $this->panneRepository->create([
            'sirene_id' => $sireneId,
            'description' => $data['description'],
            'statut' => StatutPanne::EN_ATTENTE,
        ]);

        // Notifier les techniciens
        $this->notifyTechniciensOfNewPanne($panne);

        return $panne;
    });
}
```

---

## Pagination et filtres

### Exemple : Pagination avec métadonnées

```php
public function index(Request $request): JsonResponse
{
    $ecoles = $this->ecoleService->search($request->query());

    return response()->json([
        'success' => true,
        'data' => EcoleResource::collection($ecoles->items()),
        'meta' => [
            'current_page' => $ecoles->currentPage(),
            'last_page' => $ecoles->lastPage(),
            'per_page' => $ecoles->perPage(),
            'total' => $ecoles->total(),
            'from' => $ecoles->firstItem(),
            'to' => $ecoles->lastItem(),
        ],
        'links' => [
            'first' => $ecoles->url(1),
            'last' => $ecoles->url($ecoles->lastPage()),
            'prev' => $ecoles->previousPageUrl(),
            'next' => $ecoles->nextPageUrl(),
        ],
    ]);
}
```

---

## Upload de fichiers

### Exemple : Upload de fichier avec validation

```php
<?php

namespace App\Services;

class FichierService implements FichierServiceInterface
{
    public function upload(UploadedFile $file, string $folder = 'documents'): Fichier
    {
        // Validation
        $this->validateFile($file);

        DB::beginTransaction();

        try {
            // 1. Générer un nom unique
            $filename = time() . '_' . Str::slug($file->getClientOriginalName());

            // 2. Stocker le fichier
            $path = $file->storeAs($folder, $filename, 'public');

            // 3. Créer l'enregistrement en BDD
            $fichier = Fichier::create([
                'nom_original' => $file->getClientOriginalName(),
                'nom_stockage' => $filename,
                'chemin' => $path,
                'type_mime' => $file->getMimeType(),
                'taille' => $file->getSize(),
            ]);

            DB::commit();

            return $fichier;

        } catch (\Exception $e) {
            DB::rollBack();
            // Supprimer le fichier si erreur
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        // Taille max: 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new ValidationException('Le fichier ne doit pas dépasser 10 MB.');
        }

        // Types autorisés
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new ValidationException('Type de fichier non autorisé.');
        }
    }
}
```

---

## Envoi de notifications

### Exemple : Notification multi-canal

```php
<?php

namespace App\Notifications;

use App\Enums\CanalNotification;
use App\Models\Abonnement;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbonnementExpiringNotification extends Notification
{
    public function __construct(
        private Abonnement $abonnement
    ) {}

    /**
     * Canaux de notification
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Notification par email
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre abonnement expire bientôt')
            ->greeting("Bonjour {$notifiable->nom},")
            ->line("Votre abonnement #{$this->abonnement->numero_abonnement} expire le {$this->abonnement->date_fin->format('d/m/Y')}.")
            ->action('Renouveler maintenant', url("/abonnements/{$this->abonnement->id}/renouveler"))
            ->line('Merci de votre confiance.');
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable): array
    {
        return [
            'abonnement_id' => $this->abonnement->id,
            'numero_abonnement' => $this->abonnement->numero_abonnement,
            'date_expiration' => $this->abonnement->date_fin->toDateString(),
            'message' => "Votre abonnement expire le {$this->abonnement->date_fin->format('d/m/Y')}",
        ];
    }
}
```

**Utilisation :**

```php
use Illuminate\Support\Facades\Notification;

$ecole = Ecole::find('01HQ...');
$ecole->notify(new AbonnementExpiringNotification($abonnement));

// Ou notification groupée
$ecoles = Ecole::whereHas('abonnements', function ($q) {
    $q->where('date_fin', '<=', now()->addDays(30));
})->get();

Notification::send($ecoles, new AbonnementExpiringNotification($abonnement));
```

---

## Middleware personnalisés

### Exemple : Middleware de vérification d'abonnement actif

```php
<?php

namespace App\Http\Middleware;

use App\Enums\StatutAbonnement;
use Closure;
use Illuminate\Http\Request;

class EnsureEcoleHasActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est une école
        if ($user->type !== TypeUtilisateur::ECOLE) {
            return $next($request);
        }

        // Récupérer l'école
        $ecole = $user->userable;

        // Vérifier l'abonnement actif
        $hasActiveSubscription = $ecole->abonnements()
            ->where('statut', StatutAbonnement::ACTIF)
            ->where('date_fin', '>', now())
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'Votre abonnement a expiré. Veuillez renouveler votre abonnement.',
                'code' => 'SUBSCRIPTION_EXPIRED',
            ], 403);
        }

        return $next($request);
    }
}
```

**Enregistrement :**

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ...
    'subscription.active' => \App\Http\Middleware\EnsureEcoleHasActiveSubscription::class,
];
```

**Utilisation :**

```php
// routes/api.php
Route::middleware(['auth:api', 'subscription.active'])->group(function () {
    Route::get('/sirenes', [SireneController::class, 'index']);
    Route::post('/pannes', [PanneController::class, 'store']);
});
```

---

## Résumé

Ces exemples montrent :

✅ **Repository** : Recherche avancée, filtres, eager loading
✅ **Service** : Logique métier complexe, orchestration
✅ **Relations** : Polymorphisme, Many-to-Many
✅ **Validation** : Conditionnelle, règles custom
✅ **Transactions** : Gestion d'erreurs métier
✅ **Pagination** : Avec métadonnées complètes
✅ **Upload** : Validation et stockage sécurisés
✅ **Notifications** : Multi-canal (email, BDD)
✅ **Middleware** : Logique de vérification métier

---

## Prochaines étapes

📖 Consultez aussi :
- [Architecture SOLID](ARCHITECTURE.md)
- [Guide de développement](DEV_GUIDE.md)
- [Bonnes pratiques](BEST_PRACTICES.md)
