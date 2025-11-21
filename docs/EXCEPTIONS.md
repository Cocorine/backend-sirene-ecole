# Guide de Gestion des Exceptions

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Types d'exceptions](#types-dexceptions)
3. [Handler d'exceptions global](#handler-dexceptions-global)
4. [Exceptions personnalisées](#exceptions-personnalisées)
5. [Exceptions métier](#exceptions-métier)
6. [Exceptions de validation](#exceptions-de-validation)
7. [Exceptions HTTP](#exceptions-http)
8. [Formater les réponses d'erreur](#formater-les-réponses-derreur)
9. [Logger les exceptions](#logger-les-exceptions)
10. [Exceptions par contexte](#exceptions-par-contexte)
11. [Exemples du projet](#exemples-du-projet)
12. [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

### 🎯 Qu'est-ce qu'une Exception ?

Une **Exception** est un événement qui se produit pendant l'exécution d'un programme et qui perturbe le flux normal d'exécution.

### 📊 Flow de gestion d'exception

```
┌──────────────────┐
│   APPLICATION    │
│   Exécution...   │
└────────┬─────────┘
         │
         ▼
    ⚠️ Exception lancée
         │
         ▼
┌──────────────────────────────┐
│   Exception Handler          │
│   (app/Exceptions/Handler)   │
│                              │
│   1. Intercepter             │
│   2. Logger si nécessaire    │
│   3. Formater la réponse     │
│   4. Retourner au client     │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────┐
│  JSON Response   │
│  {               │
│    "success": false,
│    "message": "...",
│    "code": "..."  │
│  }               │
└──────────────────┘
```

---

## Types d'exceptions

### 1️⃣ Exceptions Laravel natives

Laravel fournit plusieurs exceptions prêtes à l'emploi :

```php
// Ressource non trouvée (404)
use Illuminate\Database\Eloquent\ModelNotFoundException;
throw new ModelNotFoundException();

// Non autorisé (403)
use Illuminate\Auth\Access\AuthorizationException;
throw new AuthorizationException('Action non autorisée.');

// Non authentifié (401)
use Illuminate\Auth\AuthenticationException;
throw new AuthenticationException('Non authentifié.');

// Validation échouée (422)
use Illuminate\Validation\ValidationException;
throw ValidationException::withMessages([
    'email' => 'Email invalide.',
]);

// Throttle (429)
use Illuminate\Http\Exceptions\ThrottleRequestsException;
throw new ThrottleRequestsException('Trop de requêtes.');

// Méthode HTTP non autorisée (405)
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
throw new MethodNotAllowedHttpException(['GET', 'POST']);

// Service indisponible (503)
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
throw new ServiceUnavailableHttpException(60, 'Service temporairement indisponible.');
```

---

### 2️⃣ Exceptions personnalisées

Créer vos propres exceptions pour les cas métier spécifiques.

```bash
php artisan make:exception BusinessException
```

---

## Handler d'exceptions global

### 📁 Fichier : `app/Exceptions/Handler.php`

C'est le **point central** pour gérer toutes les exceptions de l'application.

```php
<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions qui ne doivent pas être loggées
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
    ];

    /**
     * Enregistrer les exceptions
     */
    public function register(): void
    {
        // Exception personnalisée métier
        $this->renderable(function (BusinessException $e, $request) {
            return $this->handleBusinessException($e, $request);
        });

        // Model non trouvé
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return $this->handleModelNotFoundException($e, $request);
        });

        // Autorisation
        $this->renderable(function (AuthorizationException $e, $request) {
            return $this->handleAuthorizationException($e, $request);
        });
    }

    /**
     * Render une exception en réponse HTTP
     */
    public function render($request, Throwable $e): JsonResponse
    {
        // Pour les requêtes API, toujours retourner JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Rendre une exception en JSON
     */
    protected function renderJsonException($request, Throwable $e): JsonResponse
    {
        // Exception HTTP (404, 403, etc.)
        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Erreur HTTP',
                'code' => $this->getErrorCode($e),
            ], $e->getStatusCode());
        }

        // Exception de validation
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        }

        // Exception d'authentification
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Exception d'autorisation
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Action non autorisée.',
                'code' => 'UNAUTHORIZED',
            ], 403);
        }

        // Model non trouvé
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Ressource non trouvée.',
                'code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        // Exception métier personnalisée
        if ($e instanceof BusinessException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'errors' => $e->getErrors(),
            ], $e->getStatusCode());
        }

        // Exception générique en production
        if (config('app.env') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur.',
                'code' => 'INTERNAL_SERVER_ERROR',
            ], 500);
        }

        // En développement, montrer les détails
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ], 500);
    }

    /**
     * Gérer BusinessException
     */
    protected function handleBusinessException(BusinessException $e, $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'errors' => $e->getErrors(),
        ], $e->getStatusCode());
    }

    /**
     * Gérer ModelNotFoundException
     */
    protected function handleModelNotFoundException(ModelNotFoundException $e, $request): JsonResponse
    {
        $model = class_basename($e->getModel());

        return response()->json([
            'success' => false,
            'message' => "{$model} non trouvé(e).",
            'code' => 'RESOURCE_NOT_FOUND',
        ], 404);
    }

    /**
     * Gérer AuthorizationException
     */
    protected function handleAuthorizationException(AuthorizationException $e, $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Action non autorisée.',
            'code' => 'UNAUTHORIZED',
        ], 403);
    }

    /**
     * Obtenir un code d'erreur
     */
    protected function getErrorCode(Throwable $e): string
    {
        return match($e->getStatusCode()) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            422 => 'VALIDATION_ERROR',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'INTERNAL_SERVER_ERROR',
            503 => 'SERVICE_UNAVAILABLE',
            default => 'ERROR',
        };
    }
}
```

---

## Exceptions personnalisées

### 1️⃣ BusinessException (Exception métier générique)

```bash
php artisan make:exception BusinessException
```

**Fichier : `app/Exceptions/BusinessException.php`**

```php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{
    /**
     * Erreurs détaillées
     */
    protected array $errors = [];

    /**
     * Code HTTP
     */
    protected int $statusCode = 400;

    /**
     * Constructeur
     */
    public function __construct(
        string $message,
        array $errors = [],
        int $statusCode = 400,
        string $code = 'BUSINESS_ERROR'
    ) {
        parent::__construct($message, 0);
        $this->errors = $errors;
        $this->statusCode = $statusCode;
        $this->code = $code;
    }

    /**
     * Obtenir les erreurs
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtenir le code HTTP
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render l'exception en réponse
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'errors' => $this->errors,
        ], $this->statusCode);
    }

    /**
     * Logger l'exception
     */
    public function report(): void
    {
        \Log::warning('Business Exception', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'errors' => $this->errors,
        ]);
    }
}
```

**Utilisation :**

```php
// Dans un Service
if ($ecole->abonnements()->where('statut', 'actif')->count() >= 5) {
    throw new BusinessException(
        'Cette école a atteint le nombre maximum d\'abonnements actifs.',
        ['max_abonnements' => 5],
        400,
        'MAX_ABONNEMENTS_REACHED'
    );
}
```

---

### 2️⃣ Exceptions spécifiques au domaine

#### SubscriptionException

```php
<?php

namespace App\Exceptions;

class SubscriptionException extends BusinessException
{
    /**
     * Abonnement expiré
     */
    public static function expired(string $abonnementId): self
    {
        return new self(
            'Votre abonnement a expiré. Veuillez le renouveler.',
            ['abonnement_id' => $abonnementId],
            403,
            'SUBSCRIPTION_EXPIRED'
        );
    }

    /**
     * Abonnement suspendu
     */
    public static function suspended(string $abonnementId, string $reason): self
    {
        return new self(
            'Votre abonnement est suspendu : ' . $reason,
            ['abonnement_id' => $abonnementId, 'reason' => $reason],
            403,
            'SUBSCRIPTION_SUSPENDED'
        );
    }

    /**
     * Renouvellement trop tôt
     */
    public static function renewalTooEarly(int $daysRemaining): self
    {
        return new self(
            "Vous pourrez renouveler votre abonnement dans {$daysRemaining} jours.",
            ['days_remaining' => $daysRemaining],
            400,
            'RENEWAL_TOO_EARLY'
        );
    }
}
```

**Utilisation :**

```php
// Dans AbonnementService
public function renouveler(string $abonnementId): Abonnement
{
    $abonnement = $this->abonnementRepository->find($abonnementId);

    if ($abonnement->statut === StatutAbonnement::SUSPENDU) {
        throw SubscriptionException::suspended($abonnementId, 'Paiement en retard');
    }

    $joursRestants = now()->diffInDays($abonnement->date_fin, false);

    if ($joursRestants > 30) {
        throw SubscriptionException::renewalTooEarly($joursRestants);
    }

    // Renouvellement...
}
```

---

#### PaymentException

```php
<?php

namespace App\Exceptions;

class PaymentException extends BusinessException
{
    /**
     * Paiement échoué
     */
    public static function failed(string $reason, array $details = []): self
    {
        return new self(
            'Le paiement a échoué : ' . $reason,
            $details,
            400,
            'PAYMENT_FAILED'
        );
    }

    /**
     * Montant invalide
     */
    public static function invalidAmount(float $expected, float $received): self
    {
        return new self(
            "Le montant payé ({$received} FCFA) ne correspond pas au montant attendu ({$expected} FCFA).",
            ['expected' => $expected, 'received' => $received],
            400,
            'INVALID_AMOUNT'
        );
    }

    /**
     * Paiement déjà effectué
     */
    public static function alreadyPaid(string $abonnementId): self
    {
        return new self(
            'Ce paiement a déjà été effectué.',
            ['abonnement_id' => $abonnementId],
            400,
            'ALREADY_PAID'
        );
    }
}
```

---

#### ResourceNotFoundException

```php
<?php

namespace App\Exceptions;

class ResourceNotFoundException extends BusinessException
{
    /**
     * Constructeur
     */
    public function __construct(string $resource, string $identifier = '')
    {
        $message = $identifier
            ? "{$resource} avec l'identifiant '{$identifier}' non trouvé(e)."
            : "{$resource} non trouvé(e).";

        parent::__construct($message, [], 404, 'RESOURCE_NOT_FOUND');
    }

    /**
     * École non trouvée
     */
    public static function ecole(string $id): self
    {
        return new self('École', $id);
    }

    /**
     * Sirène non trouvée
     */
    public static function sirene(string $id): self
    {
        return new self('Sirène', $id);
    }

    /**
     * Abonnement non trouvé
     */
    public static function abonnement(string $id): self
    {
        return new self('Abonnement', $id);
    }
}
```

**Utilisation :**

```php
// Dans un Service
public function find(string $id): Ecole
{
    $ecole = $this->ecoleRepository->find($id);

    if (!$ecole) {
        throw ResourceNotFoundException::ecole($id);
    }

    return $ecole;
}
```

---

## Exceptions métier

### Exemples d'exceptions métier courantes

#### 1️⃣ Dans EcoleService

```php
class EcoleService implements EcoleServiceInterface
{
    public function update(string $id, array $data): Ecole
    {
        $ecole = $this->ecoleRepository->find($id);

        if (!$ecole) {
            throw ResourceNotFoundException::ecole($id);
        }

        // Règle métier : École ne peut pas changer de ville si elle a des sirènes
        if (isset($data['ville_id']) && $data['ville_id'] !== $ecole->ville_id) {
            if ($ecole->sirenes()->exists()) {
                throw new BusinessException(
                    'Impossible de changer la ville car cette école possède des sirènes.',
                    ['sirenes_count' => $ecole->sirenes()->count()],
                    400,
                    'ECOLE_HAS_SIRENES'
                );
            }
        }

        return $this->ecoleRepository->update($id, $data);
    }
}
```

---

#### 2️⃣ Dans SireneService

```php
class SireneService implements SireneServiceInterface
{
    public function create(array $data): Sirene
    {
        $ecole = $this->ecoleRepository->find($data['ecole_id']);

        // Vérifier que l'école a un abonnement actif
        if (!$ecole->hasActiveAbonnement()) {
            throw SubscriptionException::expired($ecole->id);
        }

        // Vérifier le nombre maximum de sirènes
        $maxSirenes = config('sirenes.max_per_ecole', 10);

        if ($ecole->sirenes()->count() >= $maxSirenes) {
            throw new BusinessException(
                "Cette école a atteint le nombre maximum de sirènes ({$maxSirenes}).",
                ['max_sirenes' => $maxSirenes],
                400,
                'MAX_SIRENES_REACHED'
            );
        }

        return $this->sireneRepository->create($data);
    }
}
```

---

## Exceptions de validation

### 1️⃣ ValidationException dans un Service

```php
use Illuminate\Validation\ValidationException;

class PaiementService implements PaiementServiceInterface
{
    public function create(array $data): Paiement
    {
        $abonnement = $this->abonnementRepository->find($data['abonnement_id']);

        // Validation métier
        if ($data['montant'] != $abonnement->montant) {
            throw ValidationException::withMessages([
                'montant' => [
                    "Le montant doit être de {$abonnement->montant} FCFA."
                ]
            ]);
        }

        return $this->paiementRepository->create($data);
    }
}
```

---

### 2️⃣ Validation personnalisée

```php
class CustomValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Erreur de validation');
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $this->errors,
        ], 422);
    }
}
```

---

## Exceptions HTTP

### Utiliser les exceptions HTTP de Symfony

```php
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// 400 Bad Request
throw new BadRequestHttpException('Requête invalide.');

// 401 Unauthorized
throw new UnauthorizedHttpException('Bearer', 'Token invalide.');

// 403 Forbidden
throw new AccessDeniedHttpException('Accès refusé.');

// 404 Not Found
throw new NotFoundHttpException('Ressource non trouvée.');

// 429 Too Many Requests
throw new HttpException(429, 'Trop de requêtes.');

// 503 Service Unavailable
throw new HttpException(503, 'Service temporairement indisponible.');
```

---

## Formater les réponses d'erreur

### 1️⃣ Structure de réponse standardisée

```php
// Réponse d'erreur standard
{
    "success": false,
    "message": "Message d'erreur principal",
    "code": "ERROR_CODE",
    "errors": {
        "field": ["Message d'erreur spécifique"]
    },
    "debug": {  // Seulement en développement
        "exception": "App\\Exceptions\\BusinessException",
        "file": "/path/to/file.php",
        "line": 42,
        "trace": [...]
    }
}
```

---

### 2️⃣ Trait pour formater les erreurs

**Fichier : `app/Traits/FormatsExceptionResponse.php`**

```php
<?php

namespace App\Traits;

use Throwable;

trait FormatsExceptionResponse
{
    /**
     * Formater une réponse d'erreur
     */
    protected function formatExceptionResponse(
        Throwable $e,
        int $statusCode = 500,
        string $code = 'ERROR'
    ): array {
        $response = [
            'success' => false,
            'message' => $e->getMessage() ?: 'Une erreur est survenue',
            'code' => $code,
        ];

        // Ajouter les erreurs si disponibles
        if (method_exists($e, 'getErrors')) {
            $response['errors'] = $e->getErrors();
        }

        // Informations de debug en développement
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ];
        }

        return $response;
    }
}
```

---

## Logger les exceptions

### 1️⃣ Logger automatiquement dans Handler

```php
// app/Exceptions/Handler.php

use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * Reporter une exception
     */
    public function report(Throwable $e): void
    {
        // Logger les erreurs critiques
        if ($this->shouldReport($e)) {
            Log::error('Exception interceptée', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
            ]);
        }

        parent::report($e);
    }
}
```

---

### 2️⃣ Logger dans les exceptions personnalisées

```php
class BusinessException extends Exception
{
    public function report(): void
    {
        Log::warning('Business Exception', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'errors' => $this->errors,
            'user_id' => auth()->id(),
        ]);
    }
}
```

---

### 3️⃣ Niveaux de log selon la gravité

```php
class Handler extends ExceptionHandler
{
    public function report(Throwable $e): void
    {
        // Critique : Erreurs système
        if ($e instanceof \RuntimeException) {
            Log::critical('Runtime Exception', $this->context($e));
        }

        // Erreur : Exceptions métier importantes
        elseif ($e instanceof BusinessException && $e->getStatusCode() >= 500) {
            Log::error('Business Exception', $this->context($e));
        }

        // Warning : Exceptions métier mineures
        elseif ($e instanceof BusinessException) {
            Log::warning('Business Exception', $this->context($e));
        }

        // Info : Exceptions d'authentification/autorisation
        elseif ($e instanceof AuthenticationException || $e instanceof AuthorizationException) {
            Log::info('Access Exception', $this->context($e));
        }

        parent::report($e);
    }

    protected function context(Throwable $e): array
    {
        return [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ];
    }
}
```

---

## Exceptions par contexte

### 1️⃣ Exceptions pour API vs Web

```php
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Requêtes API : toujours JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderJsonException($request, $e);
        }

        // Requêtes Web : vues HTML
        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        return parent::render($request, $e);
    }
}
```

---

### 2️⃣ Messages différents selon l'environnement

```php
protected function renderJsonException($request, Throwable $e): JsonResponse
{
    $message = $e->getMessage();

    // En production, masquer les messages techniques
    if (config('app.env') === 'production' && !($e instanceof BusinessException)) {
        $message = 'Une erreur est survenue. Veuillez réessayer.';
    }

    return response()->json([
        'success' => false,
        'message' => $message,
        'code' => $this->getErrorCode($e),
    ], $this->getStatusCode($e));
}
```

---

## Exemples du projet

### Exemple 1 : EcoleService avec gestion d'erreurs

```php
<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Ecole;
use App\Repositories\Contracts\EcoleRepositoryInterface;
use App\Services\Contracts\EcoleServiceInterface;
use Illuminate\Support\Facades\DB;

class EcoleService implements EcoleServiceInterface
{
    public function __construct(
        private EcoleRepositoryInterface $ecoleRepository
    ) {}

    public function create(array $data): Ecole
    {
        // Vérifier l'unicité de l'email
        if ($this->ecoleRepository->emailExists($data['email'])) {
            throw new BusinessException(
                'Cette adresse email est déjà utilisée.',
                ['email' => $data['email']],
                400,
                'EMAIL_ALREADY_EXISTS'
            );
        }

        DB::beginTransaction();

        try {
            $ecole = $this->ecoleRepository->create($data);
            $ecole->generateCodeEtablissement();

            DB::commit();

            return $ecole;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data): Ecole
    {
        $ecole = $this->ecoleRepository->find($id);

        if (!$ecole) {
            throw ResourceNotFoundException::ecole($id);
        }

        // Règle métier
        if (isset($data['ville_id']) && $ecole->sirenes()->exists()) {
            throw new BusinessException(
                'Impossible de changer la ville car cette école possède des sirènes.',
                ['sirenes_count' => $ecole->sirenes()->count()],
                400,
                'ECOLE_HAS_SIRENES'
            );
        }

        return $this->ecoleRepository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        $ecole = $this->ecoleRepository->find($id);

        if (!$ecole) {
            throw ResourceNotFoundException::ecole($id);
        }

        // Vérifier qu'il n'y a pas d'abonnements actifs
        if ($ecole->abonnements()->where('statut', 'actif')->exists()) {
            throw new BusinessException(
                'Impossible de supprimer une école avec des abonnements actifs.',
                ['abonnements_actifs' => $ecole->abonnements()->where('statut', 'actif')->count()],
                400,
                'ECOLE_HAS_ACTIVE_ABONNEMENTS'
            );
        }

        return $this->ecoleRepository->delete($id);
    }
}
```

---

### Exemple 2 : Controller avec try-catch

```php
<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BusinessException;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEcoleRequest;
use App\Http\Resources\EcoleResource;
use App\Services\Contracts\EcoleServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EcoleController extends Controller
{
    use JsonResponseTrait;

    public function __construct(
        private readonly EcoleServiceInterface $ecoleService
    ) {}

    public function store(CreateEcoleRequest $request): JsonResponse
    {
        try {
            $ecole = $this->ecoleService->create($request->validated());

            return $this->createdResponse(
                new EcoleResource($ecole),
                'École créée avec succès'
            );

        } catch (BusinessException $e) {
            // Les BusinessException sont déjà bien formatées
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'errors' => $e->getErrors(),
            ], $e->getStatusCode());

        } catch (\Exception $e) {
            // Logger l'erreur inattendue
            Log::error('Erreur lors de la création d\'une école', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Une erreur est survenue lors de la création.',
                500
            );
        }
    }
}
```

---

## Bonnes pratiques

### ✅ À FAIRE

#### 1. Utiliser des exceptions spécifiques

```php
// ✅ BON : Exception spécifique et claire
throw SubscriptionException::expired($abonnementId);

// ❌ MAUVAIS : Exception générique
throw new Exception('Error');
```

---

#### 2. Fournir des messages clairs et actionnables

```php
// ✅ BON : Message clair avec solution
throw new BusinessException(
    'Votre abonnement a expiré le ' . $abonnement->date_fin->format('d/m/Y') . '. Veuillez le renouveler pour continuer.',
    ['abonnement_id' => $abonnement->id],
    403,
    'SUBSCRIPTION_EXPIRED'
);

// ❌ MAUVAIS : Message vague
throw new Exception('Not allowed');
```

---

#### 3. Inclure des codes d'erreur

```php
// ✅ BON : Code d'erreur pour le frontend
throw new BusinessException(
    'Limite atteinte',
    [],
    400,
    'MAX_SIRENES_REACHED'  // Le frontend peut gérer ce code spécifiquement
);

// ❌ MAUVAIS : Pas de code
throw new Exception('Too many');
```

---

#### 4. Logger selon la gravité

```php
// ✅ BON : Logger les erreurs importantes
catch (BusinessException $e) {
    Log::warning('Business rule violation', [
        'message' => $e->getMessage(),
        'user_id' => auth()->id(),
    ]);
    throw $e;
}

catch (\Exception $e) {
    Log::error('Unexpected error', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
    ]);
    throw $e;
}
```

---

### ❌ À ÉVITER

#### 1. Cacher les exceptions sans traiter

```php
// ❌ MAUVAIS : Masquer l'erreur
try {
    $result = $this->service->create($data);
} catch (\Exception $e) {
    // Ne rien faire ⚠️ L'erreur est perdue
}

// ✅ BON : Logger et relancer ou gérer
try {
    $result = $this->service->create($data);
} catch (\Exception $e) {
    Log::error('Creation failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

---

#### 2. Utiliser Exception générique

```php
// ❌ MAUVAIS : Exception générique
throw new \Exception('Something went wrong');

// ✅ BON : Exception spécifique
throw new BusinessException('Message clair', [], 400, 'SPECIFIC_CODE');
```

---

#### 3. Exposer des détails techniques en production

```php
// ❌ MAUVAIS : Détails techniques exposés
return response()->json([
    'error' => $e->getMessage(),  // Peut contenir "SQLSTATE[42S02]: Table 'users' doesn't exist"
    'trace' => $e->getTraceAsString(),
], 500);

// ✅ BON : Message générique en production
if (config('app.env') === 'production') {
    return response()->json([
        'message' => 'Une erreur est survenue.',
    ], 500);
}
```

---

## Résumé

### 🎯 Hiérarchie des exceptions

```
Exception (PHP native)
├── BusinessException (métier générique)
│   ├── SubscriptionException
│   ├── PaymentException
│   └── ResourceNotFoundException
├── ValidationException (Laravel)
├── AuthenticationException (Laravel)
├── AuthorizationException (Laravel)
└── HttpException (Symfony)
    ├── NotFoundHttpException (404)
    ├── BadRequestHttpException (400)
    └── AccessDeniedHttpException (403)
```

---

### 📋 Checklist gestion d'exceptions

Avant de lancer une exception :

- [ ] L'exception a un **type spécifique** (pas Exception générique)
- [ ] Le message est **clair et actionnable**
- [ ] Un **code d'erreur** est fourni
- [ ] Le **code HTTP** est approprié (400, 403, 404, 422, 500)
- [ ] Les **détails** sont inclus si pertinents
- [ ] L'exception est **loggée** si nécessaire
- [ ] Le message est **sécurisé** (pas de détails techniques en prod)
- [ ] L'exception est **documentée** (PHPDoc @throws)

---

## Prochaines étapes

📖 Consultez aussi :
- [ARCHITECTURE.md](ARCHITECTURE.md) - Principes SOLID
- [BEST_PRACTICES.md](BEST_PRACTICES.md) - Bonnes pratiques
- [MIDDLEWARE.md](MIDDLEWARE.md) - Gestion des erreurs dans middleware
- [CONTROLLERS_INJECTION.md](CONTROLLERS_INJECTION.md) - Try-catch dans controllers
