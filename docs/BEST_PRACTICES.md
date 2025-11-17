# Bonnes Pratiques et Conventions

## 📚 Table des matières

1. [Conventions de nommage](#conventions-de-nommage)
2. [Structure du code](#structure-du-code)
3. [Typage et documentation](#typage-et-documentation)
4. [Gestion des erreurs](#gestion-des-erreurs)
5. [Sécurité](#sécurité)
6. [Performance](#performance)
7. [Tests](#tests)
8. [Git et versioning](#git-et-versioning)
9. [Code Review Checklist](#code-review-checklist)

---

## Conventions de nommage

### Classes et Fichiers

```
✅ Bonnes pratiques                    ❌ À éviter

PascalCase pour les classes           snake_case pour les classes
EcoleService                          ecole_service
UserController                        usercontroller

Noms descriptifs                      Noms abrégés
AbonnementRepository                  AbonRepo
CreateEcoleRequest                    EcoleReq

Un fichier = une classe               Plusieurs classes par fichier
EcoleService.php                      Services.php (avec 10 classes)
```

### Variables et Méthodes

```php
// ✅ BON : camelCase pour variables et méthodes
$userName = 'John';
$ecoleActives = Ecole::actif()->get();

public function getUserInfo(): UserInfo
{
    return $this->user->userInfo;
}

// ❌ MAUVAIS : snake_case ou PascalCase
$user_name = 'John';
$EcoleActives = Ecole::actif()->get();

public function get_user_info(): UserInfo
{
    return $this->user->user_info;
}
```

### Constantes et Enums

```php
// ✅ BON : SCREAMING_SNAKE_CASE pour constantes
const MAX_UPLOAD_SIZE = 10485760; // 10 MB
const SUBSCRIPTION_DURATION_YEARS = 1;

// ✅ BON : PascalCase pour Enums, SCREAMING_SNAKE_CASE pour les valeurs
enum StatutAbonnement: string
{
    case ACTIF = 'actif';
    case EXPIRE = 'expire';
    case SUSPENDU = 'suspendu';
}

// ❌ MAUVAIS
const maxUploadSize = 10485760;
const subscription-duration = 1;
```

### Routes

```php
// ✅ BON : kebab-case, noms au pluriel pour ressources
Route::get('/ecoles', [EcoleController::class, 'index']);
Route::post('/abonnements/{id}/renouveler', [AbonnementController::class, 'renouveler']);
Route::get('/users/{id}/user-info', [UserController::class, 'getUserInfo']);

// ❌ MAUVAIS : camelCase ou snake_case
Route::get('/Ecoles', [EcoleController::class, 'index']);
Route::post('/abonnement_renouveler', [AbonnementController::class, 'renouveler']);
Route::get('/getUserInfo', [UserController::class, 'getUserInfo']);
```

### Tables de base de données

```php
// ✅ BON : snake_case, noms au pluriel
Schema::create('ecoles', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('nom');
    $table->string('code_etablissement')->unique();
    $table->foreignUlid('ville_id')->constrained('villes');
});

// ❌ MAUVAIS : camelCase ou singulier
Schema::create('Ecole', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('Nom');
    $table->string('CodeEtablissement')->unique();
});
```

---

## Structure du code

### Organisation des imports

```php
<?php

namespace App\Services;

// 1. Classes Laravel (alphabétique)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// 2. Classes de l'application (alphabétique)
use App\Enums\StatutAbonnement;
use App\Models\Abonnement;
use App\Notifications\AbonnementNotification;
use App\Repositories\Contracts\AbonnementRepositoryInterface;
use App\Services\Contracts\AbonnementServiceInterface;

class AbonnementService implements AbonnementServiceInterface
{
    // ...
}
```

### Ordre des éléments dans une classe

```php
<?php

namespace App\Models;

class Ecole extends Model
{
    // 1. Traits
    use HasFactory, SoftDeletes, HasUlid;

    // 2. Constantes
    const CODE_PREFIX = 'ECO';
    const MAX_SITES = 10;

    // 3. Propriétés
    protected $table = 'ecoles';
    protected $fillable = ['nom', 'email'];
    protected $casts = ['statut' => StatutEcole::class];

    // 4. Relations
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    // 5. Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', StatutEcole::ACTIVE);
    }

    // 6. Accessors & Mutators
    public function getNomCompletAttribute(): string
    {
        return $this->nom . ' - ' . $this->ville->nom;
    }

    // 7. Méthodes publiques
    public function generateCodeEtablissement(): void
    {
        // ...
    }

    // 8. Méthodes privées/protégées
    private function formatCode(string $code): string
    {
        // ...
    }
}
```

### Controller bien structuré

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEcoleRequest;
use App\Http\Requests\UpdateEcoleRequest;
use App\Http\Resources\EcoleResource;
use App\Services\Contracts\EcoleServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    use JsonResponseTrait;

    // 1. Constructor avec injection de dépendances
    public function __construct(
        private EcoleServiceInterface $ecoleService
    ) {}

    // 2. Méthodes CRUD (ordre standard)
    public function index(Request $request): JsonResponse
    {
        // Liste
    }

    public function show(string $id): JsonResponse
    {
        // Détail
    }

    public function store(CreateEcoleRequest $request): JsonResponse
    {
        // Création
    }

    public function update(UpdateEcoleRequest $request, string $id): JsonResponse
    {
        // Mise à jour
    }

    public function destroy(string $id): JsonResponse
    {
        // Suppression
    }

    // 3. Méthodes personnalisées (après CRUD)
    public function activer(string $id): JsonResponse
    {
        // Action spécifique
    }

    // 4. Méthodes privées (à la fin)
    private function formatResponse($data): array
    {
        // Helper privé
    }
}
```

---

## Typage et documentation

### Typage strict

```php
// ✅ BON : Typage strict activé
<?php

declare(strict_types=1);

namespace App\Services;

class EcoleService implements EcoleServiceInterface
{
    // Type hints pour les paramètres
    public function create(array $data): Ecole
    {
        // ...
    }

    // Type nullable explicite
    public function find(string $id): ?Ecole
    {
        return $this->ecoleRepository->find($id);
    }

    // Type union (PHP 8.0+)
    public function getStatistics(): array|Collection
    {
        // ...
    }
}

// ❌ MAUVAIS : Pas de typage
class EcoleService
{
    public function create($data)
    {
        // ...
    }
}
```

### Documentation PHPDoc

```php
/**
 * Créer une nouvelle école avec abonnement initial
 *
 * Cette méthode crée une école, génère son code établissement,
 * et lui attribue un abonnement d'un an.
 *
 * @param array $data Les données de l'école
 * @param bool $withAbonnement Créer l'abonnement initial (défaut: true)
 *
 * @return Ecole L'école créée avec ses relations
 *
 * @throws ValidationException Si les données sont invalides
 * @throws BusinessException Si le code établissement existe déjà
 *
 * @example
 * $ecole = $service->create([
 *     'nom' => 'École Primaire',
 *     'email' => 'contact@ecole.fr',
 *     'ville_id' => '01HQ...'
 * ]);
 */
public function create(array $data, bool $withAbonnement = true): Ecole
{
    // ...
}
```

### Annotations pour l'IDE

```php
/**
 * @property-read string $id
 * @property string $nom
 * @property string $email
 * @property StatutEcole $statut
 *
 * @property-read Ville $ville
 * @property-read Collection|Abonnement[] $abonnements
 *
 * @method static Builder actif()
 * @method static Builder inactif()
 */
class Ecole extends Model
{
    // ...
}
```

---

## Gestion des erreurs

### Exceptions métier

```php
// ✅ BON : Exceptions spécifiques et descriptives
if (!$this->abonnementRepository->canRenew($abonnementId)) {
    throw new BusinessException(
        'Cet abonnement ne peut pas être renouvelé car il n\'est pas encore arrivé à expiration.',
        ['abonnement_id' => $abonnementId, 'date_fin' => $abonnement->date_fin]
    );
}

// ❌ MAUVAIS : Exception générique
if (!$this->abonnementRepository->canRenew($abonnementId)) {
    throw new Exception('Erreur');
}
```

### Validation métier dans le Service

```php
// ✅ BON : Validation métier dans le Service
class EcoleService
{
    public function create(array $data): Ecole
    {
        // Validation métier
        if ($this->ecoleRepository->emailExists($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'Cet email est déjà utilisé par une autre école.'
            ]);
        }

        // Logique métier
        return $this->ecoleRepository->create($data);
    }
}

// ❌ MAUVAIS : Validation métier dans le Controller
class EcoleController
{
    public function store(CreateEcoleRequest $request)
    {
        // ⚠️ Ne pas mettre de logique métier ici
        if ($this->ecoleService->emailExists($request->email)) {
            return $this->errorResponse('Email déjà utilisé');
        }
    }
}
```

### Try-Catch approprié

```php
// ✅ BON : Catch spécifique et gestion appropriée
try {
    $ecole = $this->ecoleService->create($data);
    return $this->createdResponse(new EcoleResource($ecole));

} catch (ValidationException $e) {
    return $this->validationErrorResponse($e->errors());

} catch (BusinessException $e) {
    return $this->errorResponse($e->getMessage(), 400);

} catch (\Exception $e) {
    Log::error('Erreur création école', [
        'data' => $data,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return $this->errorResponse('Erreur interne du serveur', 500);
}

// ❌ MAUVAIS : Catch générique qui masque les erreurs
try {
    $ecole = $this->ecoleService->create($data);
} catch (\Exception $e) {
    return response()->json(['error' => 'Error'], 500);
}
```

---

## Sécurité

### Validation des entrées

```php
// ✅ BON : Validation stricte
class CreateEcoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:ecoles,email'],
            'telephone' => ['required', 'string', 'regex:/^(\+225|00225|0)?[0-9]{10}$/'],
            'ville_id' => ['required', 'exists:villes,id'],
        ];
    }
}

// ❌ MAUVAIS : Pas de validation
$ecole = Ecole::create($request->all()); // ⚠️ Mass assignment vulnérable
```

### Protection contre l'injection SQL

```php
// ✅ BON : Utiliser Eloquent ou Query Builder avec bindings
$ecoles = Ecole::where('ville_id', $villeId)->get();

$ecoles = DB::table('ecoles')
    ->where('nom', 'LIKE', '%' . $search . '%')
    ->get();

// ❌ MAUVAIS : Requête SQL brute sans bindings
$ecoles = DB::select("SELECT * FROM ecoles WHERE nom LIKE '%{$search}%'"); // ⚠️ SQL Injection
```

### Autorisation (Gates & Policies)

```php
// ✅ BON : Utiliser les Gates pour l'autorisation
Route::put('/ecoles/{id}', [EcoleController::class, 'update'])
    ->middleware('can:update-ecole');

// Dans le Controller
public function update(UpdateEcoleRequest $request, string $id)
{
    // Vérifier que l'utilisateur peut modifier CETTE école spécifique
    $ecole = Ecole::findOrFail($id);
    $this->authorize('update', $ecole);

    // ...
}

// ❌ MAUVAIS : Pas de vérification d'autorisation
public function update(UpdateEcoleRequest $request, string $id)
{
    $ecole = Ecole::findOrFail($id);
    // ⚠️ N'importe qui peut modifier n'importe quelle école
    $ecole->update($request->validated());
}
```

### Masquer les données sensibles

```php
// ✅ BON : Masquer dans le modèle
class User extends Model
{
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];
}

// ✅ BON : Masquer dans la Resource
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            // Ne pas exposer le mot de passe, token, etc.
        ];
    }
}

// ❌ MAUVAIS : Exposer toutes les données
return User::all(); // ⚠️ Expose password, tokens, etc.
```

---

## Performance

### Eager Loading (N+1 Problem)

```php
// ✅ BON : Eager loading
$ecoles = Ecole::with(['ville', 'abonnements', 'sites'])->get();

foreach ($ecoles as $ecole) {
    echo $ecole->ville->nom; // Pas de requête supplémentaire
}

// ❌ MAUVAIS : N+1 queries
$ecoles = Ecole::all(); // 1 requête

foreach ($ecoles as $ecole) {
    echo $ecole->ville->nom; // N requêtes supplémentaires
}
```

### Pagination

```php
// ✅ BON : Pagination pour grandes listes
public function index(Request $request)
{
    $ecoles = Ecole::query()
        ->with('ville')
        ->paginate($request->input('per_page', 15));

    return EcoleResource::collection($ecoles);
}

// ❌ MAUVAIS : Charger toutes les données
public function index()
{
    $ecoles = Ecole::all(); // ⚠️ Peut charger 10,000+ enregistrements
    return EcoleResource::collection($ecoles);
}
```

### Cache

```php
// ✅ BON : Cache pour données peu modifiées
public function getAllPays(): Collection
{
    return Cache::remember('pays.all', 3600, function () {
        return Pays::orderBy('nom')->get();
    });
}

// Invalider le cache lors de modifications
public function create(array $data): Pays
{
    $pays = $this->paysRepository->create($data);
    Cache::forget('pays.all');
    return $pays;
}

// ❌ MAUVAIS : Toujours requêter la BDD
public function getAllPays(): Collection
{
    return Pays::orderBy('nom')->get(); // Requête à chaque appel
}
```

### Select spécifique

```php
// ✅ BON : Sélectionner seulement les colonnes nécessaires
$ecoles = Ecole::select(['id', 'nom', 'email', 'ville_id'])
    ->with('ville:id,nom')
    ->get();

// ❌ MAUVAIS : Sélectionner toutes les colonnes
$ecoles = Ecole::with('ville')->get(); // Charge toutes les colonnes
```

---

## Tests

### Nommage des tests

```php
// ✅ BON : Nom descriptif
public function test_can_create_ecole_with_valid_data()
{
    // ...
}

public function test_cannot_create_ecole_with_duplicate_email()
{
    // ...
}

public function test_ecole_generates_code_etablissement_on_creation()
{
    // ...
}

// ❌ MAUVAIS : Nom vague
public function test_ecole()
{
    // ...
}

public function test_create()
{
    // ...
}
```

### Structure AAA (Arrange, Act, Assert)

```php
public function test_can_renew_abonnement()
{
    // ARRANGE : Préparer les données
    $ecole = Ecole::factory()->create();
    $abonnement = Abonnement::factory()->create([
        'ecole_id' => $ecole->id,
        'date_fin' => now()->addDays(10),
    ]);

    // ACT : Exécuter l'action
    $result = $this->abonnementService->renouveler($abonnement->id);

    // ASSERT : Vérifier le résultat
    $this->assertInstanceOf(Abonnement::class, $result);
    $this->assertEquals($ecole->id, $result->ecole_id);
    $this->assertTrue($result->date_debut->greaterThan($abonnement->date_fin));
    $this->assertDatabaseHas('abonnements', [
        'id' => $result->id,
        'statut' => StatutAbonnement::EN_ATTENTE->value,
    ]);
}
```

### Tests d'API

```php
public function test_authenticated_user_can_create_ecole()
{
    // Authentification
    $user = User::factory()->create();
    Passport::actingAs($user);

    // Données de test
    $data = [
        'nom' => 'École Test',
        'email' => 'test@ecole.fr',
        'telephone' => '+2250123456789',
        'ville_id' => Ville::factory()->create()->id,
    ];

    // Requête
    $response = $this->postJson('/api/ecoles', $data);

    // Assertions
    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'nom', 'email', 'code_etablissement']
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'nom' => 'École Test',
                'email' => 'test@ecole.fr',
            ]
        ]);

    // Vérification BDD
    $this->assertDatabaseHas('ecoles', [
        'nom' => 'École Test',
        'email' => 'test@ecole.fr',
    ]);
}
```

---

## Git et versioning

### Messages de commit

```bash
# ✅ BON : Messages descriptifs et structurés

# Format recommandé : <type>(<scope>): <sujet>
feat(ecole): add department relationship to ecole model
fix(abonnement): correct date calculation for renewal
refactor(repository): extract common query methods to base repository
docs(api): update swagger documentation for ecole endpoints
test(ecole): add integration tests for ecole creation

# Avec description détaillée
feat(notification): add email notification for expiring subscriptions

- Add AbonnementExpiringNotification class
- Send notification 30 days before expiration
- Add command to check and send notifications daily
- Update tests

# ❌ MAUVAIS : Messages vagues
fix bug
update
test
change file
```

### Types de commit

```
feat     : Nouvelle fonctionnalité
fix      : Correction de bug
refactor : Refactoring sans changement de fonctionnalité
docs     : Documentation
test     : Ajout/modification de tests
chore    : Tâches de maintenance (dépendances, config)
style    : Formatage du code (sans changement de logique)
perf     : Amélioration de performance
```

### Branches

```bash
# ✅ BON : Nommage descriptif
feature/department-management
fix/abonnement-renewal-bug
refactor/repository-pattern
hotfix/security-vulnerability

# ❌ MAUVAIS
branch1
fix
new-feature
```

---

## Code Review Checklist

### ✅ Avant de soumettre pour review

- [ ] Le code compile et fonctionne
- [ ] Les tests passent (unitaires et intégration)
- [ ] Pas de code commenté inutile
- [ ] Pas de console.log ou dd() oubliés
- [ ] Les conventions de nommage sont respectées
- [ ] Le code est bien formatté (PSR-12)
- [ ] La documentation est à jour
- [ ] Les migrations sont réversibles
- [ ] Les seeders/factories sont à jour
- [ ] Pas de credentials en dur dans le code

### ✅ Pendant la review

**Architecture :**
- [ ] Respect des principes SOLID
- [ ] Séparation Controller/Service/Repository
- [ ] Injection de dépendances utilisée
- [ ] Pas de logique métier dans le Controller

**Sécurité :**
- [ ] Validation des entrées (FormRequest)
- [ ] Autorisation vérifiée (Gates/Policies)
- [ ] Pas de SQL injection possible
- [ ] Données sensibles masquées

**Performance :**
- [ ] Pas de N+1 queries (eager loading)
- [ ] Pagination pour les listes
- [ ] Index sur les colonnes fréquemment recherchées
- [ ] Cache utilisé si approprié

**Tests :**
- [ ] Couverture de tests suffisante (>80%)
- [ ] Tests unitaires pour la logique métier
- [ ] Tests d'intégration pour les API
- [ ] Cas d'erreur testés

**Documentation :**
- [ ] PHPDoc à jour
- [ ] Swagger annotations
- [ ] README mis à jour si nécessaire
- [ ] CHANGELOG mis à jour

---

## Outils recommandés

### Linting et formatage

```bash
# Laravel Pint (PSR-12)
./vendor/bin/pint

# PHPStan (analyse statique)
./vendor/bin/phpstan analyse

# PHP CS Fixer
./vendor/bin/php-cs-fixer fix
```

### Configuration Pint

```json
// pint.json
{
    "preset": "psr12",
    "rules": {
        "array_syntax": {
            "syntax": "short"
        },
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "no_unused_imports": true,
        "not_operator_with_successor_space": true
    }
}
```

### IDE Configuration (VS Code)

```json
// .vscode/settings.json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php.suggest.basic": false,
    "editor.formatOnSave": true,
    "files.associations": {
        "*.php": "php"
    },
    "[php]": {
        "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
    }
}
```

---

## Résumé

### ✅ Principes fondamentaux

1. **Clarté avant optimisation** : Code lisible > Code clever
2. **DRY (Don't Repeat Yourself)** : Éviter la duplication
3. **KISS (Keep It Simple, Stupid)** : Simplicité avant tout
4. **YAGNI (You Aren't Gonna Need It)** : Ne pas sur-engineer
5. **Fail Fast** : Valider tôt, échouer rapidement
6. **Type Safety** : Typage strict partout
7. **Test Coverage** : Tester la logique métier
8. **Security First** : Sécurité dès la conception

### 🎯 Checklist quotidienne

- [ ] Code formatté (Pint)
- [ ] Tests écrits et passent
- [ ] Documentation à jour
- [ ] Pas de warnings PHPStan
- [ ] Commit avec message descriptif
- [ ] Code review avant merge

---

## Prochaines étapes

📖 Consultez aussi :
- [Architecture SOLID](ARCHITECTURE.md)
- [Guide de développement](DEV_GUIDE.md)
- [Exemples concrets](EXAMPLES.md)

💡 **Ressources externes :**
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHP The Right Way](https://phptherightway.com/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
