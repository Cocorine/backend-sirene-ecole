# Guide des Middleware Laravel

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Types de Middleware](#types-de-middleware)
3. [Créer un Middleware personnalisé](#créer-un-middleware-personnalisé)
4. [Enregistrer un Middleware](#enregistrer-un-middleware)
5. [Middleware de sécurité](#middleware-de-sécurité)
6. [Middleware d'autorisation](#middleware-dautorisation)
7. [Middleware de transformation](#middleware-de-transformation)
8. [Middleware de logging et monitoring](#middleware-de-logging-et-monitoring)
9. [Ordre d'exécution](#ordre-dexécution)
10. [Exemples du projet](#exemples-du-projet)
11. [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

### 🎯 Qu'est-ce qu'un Middleware ?

Un **Middleware** est une couche intermédiaire qui filtre les **requêtes HTTP** avant qu'elles n'atteignent le Controller, et peut également modifier les **réponses** avant qu'elles ne soient renvoyées au client.

### 📊 Flow d'une requête avec Middleware

```
┌──────────────┐
│   CLIENT     │
│  (Browser)   │
└──────┬───────┘
       │ HTTP Request
       ▼
┌─────────────────────────────────────────┐
│         MIDDLEWARE STACK                │
│                                         │
│  ┌────────────────────────────────┐   │
│  │ 1. ForceJsonResponse           │   │
│  │    → Force JSON responses      │   │
│  └────────────┬───────────────────┘   │
│               ▼                        │
│  ┌────────────────────────────────┐   │
│  │ 2. HandleOptionsRequest        │   │
│  │    → Handle CORS preflight     │   │
│  └────────────┬───────────────────┘   │
│               ▼                        │
│  ┌────────────────────────────────┐   │
│  │ 3. auth:api                    │   │
│  │    → Verify authentication     │   │
│  └────────────┬───────────────────┘   │
│               ▼                        │
│  ┌────────────────────────────────┐   │
│  │ 4. can:create-ecole            │   │
│  │    → Check authorization       │   │
│  └────────────┬───────────────────┘   │
│               ▼                        │
└───────────────┼─────────────────────────┘
                │
                ▼
       ┌────────────────┐
       │   CONTROLLER   │
       │   (Business)   │
       └────────┬───────┘
                │ Response
                ▼
       ┌────────────────┐
       │  HTTP Response │
       └────────────────┘
```

---

## Types de Middleware

### 1️⃣ Middleware Global

S'applique à **toutes les requêtes** de l'application.

**Fichier : `app/Http/Kernel.php`**

```php
protected $middleware = [
    \App\Http\Middleware\ForceJsonResponse::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
];
```

---

### 2️⃣ Middleware de Route

S'applique à des **routes spécifiques** ou **groupes de routes**.

```php
// routes/api.php
Route::middleware(['auth:api'])->group(function () {
    Route::get('/ecoles', [EcoleController::class, 'index']);
});

// Ou sur une route unique
Route::post('/ecoles', [EcoleController::class, 'store'])
    ->middleware('can:create-ecole');
```

---

### 3️⃣ Middleware Alias (nommé)

Middleware enregistré avec un **nom court** pour faciliter l'utilisation.

**Fichier : `app/Http/Kernel.php`**

```php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'role' => \App\Http\Middleware\CheckRole::class,
    'subscription.active' => \App\Http\Middleware\EnsureEcoleHasActiveSubscription::class,
];
```

**Utilisation :**

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('subscription.active');
```

---

### 4️⃣ Middleware de Groupe

Groupes prédéfinis de middleware.

**Fichier : `app/Http/Kernel.php`**

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ],

    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

---

## Créer un Middleware personnalisé

### 📝 Commande de création

```bash
php artisan make:middleware EnsureEcoleHasActiveSubscription
```

### 🏗️ Structure d'un Middleware

**Fichier : `app/Http/Middleware/EnsureEcoleHasActiveSubscription.php`**

```php
<?php

namespace App\Http\Middleware;

use App\Enums\StatutAbonnement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEcoleHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Vérifier que l'utilisateur est une école
        if ($user->type !== 'ecole') {
            return $next($request); // Passer au suivant si pas une école
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

        // ✅ Tout est OK, passer à la suite
        return $next($request);
    }
}
```

---

### 🔧 Middleware avec paramètres

**Exemple : Vérifier un rôle spécifique**

```bash
php artisan make:middleware CheckRole
```

**Fichier : `app/Http/Middleware/CheckRole.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $role  Le rôle requis
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Vérifier si l'utilisateur a le rôle
        if (!$user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => "Accès interdit. Rôle requis : {$role}",
            ], 403);
        }

        return $next($request);
    }
}
```

**Utilisation avec paramètre :**

```php
Route::post('/users', [UserController::class, 'store'])
    ->middleware('role:admin');

Route::post('/techniciens', [TechnicienController::class, 'store'])
    ->middleware('role:admin');
```

---

### 🔄 Middleware qui modifie la réponse

**Exemple : Ajouter des headers à toutes les réponses**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCustomHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Traiter la requête
        $response = $next($request);

        // ✅ Modifier la réponse APRÈS le controller
        $response->headers->set('X-Application-Version', '1.0.0');
        $response->headers->set('X-Author', 'Backend Team');
        $response->headers->set('X-Request-Id', $request->id());

        return $response;
    }
}
```

---

## Enregistrer un Middleware

### 1️⃣ Enregistrer comme Middleware Global

**Fichier : `app/Http/Kernel.php`**

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\ForceJsonResponse::class,
];
```

---

### 2️⃣ Enregistrer comme Middleware Alias

**Fichier : `app/Http/Kernel.php`**

```php
protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\CheckRole::class,
    'subscription.active' => \App\Http\Middleware\EnsureEcoleHasActiveSubscription::class,
    'log.request' => \App\Http\Middleware\LogRequest::class,
];
```

**Utilisation :**

```php
Route::middleware(['subscription.active'])->group(function () {
    Route::get('/sirenes', [SireneController::class, 'index']);
});
```

---

### 3️⃣ Enregistrer dans un Groupe

**Fichier : `app/Http/Kernel.php`**

```php
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\ForceJsonResponse::class,
        \App\Http\Middleware\HandleOptionsRequest::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

---

## Middleware de sécurité

### 1️⃣ ForceJsonResponse

Force toutes les réponses à être en JSON (utile pour les API).

**Fichier : `app/Http/Middleware/ForceJsonResponse.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Force toutes les réponses en JSON
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forcer l'acceptation de JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
```

---

### 2️⃣ HandleOptionsRequest (CORS Preflight)

Gérer les requêtes CORS OPTIONS.

**Fichier : `app/Http/Middleware/HandleOptionsRequest.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleOptionsRequest
{
    /**
     * Gérer les requêtes OPTIONS (CORS preflight)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $next($request);

        // Ajouter les headers CORS à toutes les réponses
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        return $response;
    }
}
```

---

### 3️⃣ ValidateSignature (Sécuriser les URL signées)

Vérifier que l'URL n'a pas été modifiée.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier la signature de l'URL
        if (!URL::hasValidSignature($request)) {
            return response()->json([
                'success' => false,
                'message' => 'URL invalide ou expirée.',
            ], 403);
        }

        return $next($request);
    }
}
```

**Utilisation :**

```php
// Générer une URL signée
$url = URL::temporarySignedRoute(
    'abonnement.renew',
    now()->addHours(24),
    ['id' => $abonnementId]
);

// Route avec validation
Route::post('/abonnements/{id}/renew', [AbonnementController::class, 'renew'])
    ->name('abonnement.renew')
    ->middleware('signed');
```

---

## Middleware d'autorisation

### 1️⃣ EnsureUserIsEcole

Vérifier que l'utilisateur est bien une école.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEcole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->type !== 'ecole') {
            return response()->json([
                'success' => false,
                'message' => 'Cette action est réservée aux écoles.',
            ], 403);
        }

        return $next($request);
    }
}
```

---

### 2️⃣ EnsureUserIsTechnicien

Vérifier que l'utilisateur est un technicien.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTechnicien
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->type !== 'technicien') {
            return response()->json([
                'success' => false,
                'message' => 'Cette action est réservée aux techniciens.',
            ], 403);
        }

        return $next($request);
    }
}
```

**Utilisation :**

```php
Route::middleware(['auth:api', 'user.is.technicien'])->group(function () {
    Route::get('/missions', [MissionController::class, 'index']);
    Route::post('/interventions/{id}/complete', [InterventionController::class, 'complete']);
});
```

---

## Middleware de transformation

### 1️⃣ TrimStrings

Nettoyer les espaces dans les données.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrimStrings
{
    /**
     * Champs à ne pas nettoyer
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $this->clean($request);

        return $next($request);
    }

    /**
     * Nettoyer les données de la requête
     */
    protected function clean(Request $request): void
    {
        $input = $request->all();

        foreach ($input as $key => $value) {
            if (in_array($key, $this->except)) {
                continue;
            }

            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }

        $request->merge($input);
    }
}
```

---

### 2️⃣ ConvertEmptyStringsToNull

Convertir les chaînes vides en null.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertEmptyStringsToNull
{
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
```

---

## Middleware de logging et monitoring

### 1️⃣ LogRequest

Logger toutes les requêtes API.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // Logger la requête entrante
        Log::info('Incoming API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ]);

        $response = $next($request);

        // Logger la réponse
        Log::info('API Response', [
            'status' => $response->getStatusCode(),
            'duration' => microtime(true) - LARAVEL_START,
        ]);

        return $response;
    }
}
```

---

### 2️⃣ MeasureExecutionTime

Mesurer le temps d'exécution.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MeasureExecutionTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $start;

        // Ajouter le temps d'exécution dans les headers
        $response->headers->set('X-Execution-Time', round($duration * 1000, 2) . 'ms');

        // Logger si la requête est lente (> 1 seconde)
        if ($duration > 1) {
            Log::warning('Slow API Request', [
                'url' => $request->fullUrl(),
                'duration' => $duration,
                'method' => $request->method(),
            ]);
        }

        return $response;
    }
}
```

---

### 3️⃣ TrackApiUsage

Suivre l'utilisation de l'API par utilisateur.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackApiUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $key = "api_usage:{$user->id}:" . now()->format('Y-m-d');

            // Incrémenter le compteur
            $count = Cache::increment($key);

            // Définir l'expiration à minuit
            Cache::put($key, $count, now()->endOfDay());

            // Limite : 1000 requêtes par jour
            if ($count > 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limite quotidienne d\'API atteinte. Réessayez demain.',
                ], 429);
            }
        }

        $response = $next($request);

        // Ajouter les headers de limite
        $response->headers->set('X-RateLimit-Limit', '1000');
        $response->headers->set('X-RateLimit-Remaining', 1000 - ($count ?? 0));

        return $response;
    }
}
```

---

## Ordre d'exécution

### 🔢 Ordre des Middleware

L'ordre des middleware est **important** car ils s'exécutent en cascade.

**Fichier : `app/Http/Kernel.php`**

```php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class,
    \Illuminate\Session\Middleware\AuthenticateSession::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \Illuminate\Auth\Middleware\Authorize::class,
];
```

### 📊 Ordre recommandé pour une API

```php
Route::middleware([
    'force.json',           // 1. Forcer JSON
    'cors',                 // 2. Gérer CORS
    'auth:api',             // 3. Authentification
    'subscription.active',  // 4. Vérifier abonnement
    'can:create-ecole',     // 5. Autorisation
    'log.request',          // 6. Logger
])->group(function () {
    // Routes...
});
```

---

## Exemples du projet

### Exemple 1 : Routes avec Middleware empilés

```php
// routes/api.php

// Routes publiques (pas de middleware)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/ecoles/inscription', [EcoleController::class, 'inscription']);

// Routes authentifiées
Route::middleware(['auth:api'])->group(function () {

    // Routes pour les écoles uniquement
    Route::middleware(['user.is.ecole'])->group(function () {
        Route::get('/sirenes', [SireneController::class, 'index']);
        Route::post('/sirenes/{id}/declarer-panne', [PanneController::class, 'declarer']);
    });

    // Routes pour les techniciens uniquement
    Route::middleware(['user.is.technicien'])->group(function () {
        Route::get('/missions', [MissionController::class, 'index']);
        Route::post('/interventions/{id}/complete', [InterventionController::class, 'complete']);
    });

    // Routes admin uniquement
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});

// Routes avec abonnement actif requis
Route::middleware(['auth:api', 'subscription.active'])->group(function () {
    Route::post('/sirenes', [SireneController::class, 'store']);
    Route::post('/abonnements/{id}/renouveler', [AbonnementController::class, 'renouveler']);
});
```

---

### Exemple 2 : Middleware chainés

```php
Route::post('/paiements', [PaiementController::class, 'store'])
    ->middleware([
        'auth:api',                  // 1. Authentifié
        'user.is.ecole',             // 2. Est une école
        'subscription.active',        // 3. Abonnement actif
        'can:create-paiement',       // 4. Permission
        'throttle:10,1',             // 5. Rate limiting (10 req/min)
    ]);
```

---

## Bonnes pratiques

### ✅ À FAIRE

#### 1. Retourner des réponses JSON cohérentes

```php
// ✅ BON : Réponse JSON structurée
public function handle(Request $request, Closure $next): Response
{
    if (!$user->hasActiveSubscription()) {
        return response()->json([
            'success' => false,
            'message' => 'Abonnement expiré.',
            'code' => 'SUBSCRIPTION_EXPIRED',
        ], 403);
    }

    return $next($request);
}

// ❌ MAUVAIS : Réponse inconsistante
public function handle(Request $request, Closure $next): Response
{
    if (!$user->hasActiveSubscription()) {
        return response('Forbidden', 403); // ⚠️ Pas JSON
    }
}
```

---

#### 2. Utiliser des codes d'erreur explicites

```php
// ✅ BON : Codes d'erreur clairs
return response()->json([
    'success' => false,
    'message' => 'Limite quotidienne atteinte.',
    'code' => 'RATE_LIMIT_EXCEEDED',
    'retry_after' => now()->addDay()->toISOString(),
], 429);

// ❌ MAUVAIS : Message générique
return response()->json(['error' => 'Error'], 400);
```

---

#### 3. Logger les actions importantes

```php
// ✅ BON : Logger les informations pertinentes
public function handle(Request $request, Closure $next): Response
{
    if (!$user->hasPermission('admin')) {
        Log::warning('Unauthorized access attempt', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        return response()->json(['message' => 'Forbidden'], 403);
    }

    return $next($request);
}
```

---

#### 4. Utiliser des noms de middleware descriptifs

```php
// ✅ BON : Noms explicites
'subscription.active' => EnsureEcoleHasActiveSubscription::class,
'user.is.ecole' => EnsureUserIsEcole::class,
'log.request' => LogRequest::class,

// ❌ MAUVAIS : Noms vagues
'check' => SomeMiddleware::class,
'verify' => AnotherMiddleware::class,
```

---

### ❌ À ÉVITER

#### 1. Ne pas mettre de logique métier lourde

```php
// ❌ MAUVAIS : Logique métier complexe dans middleware
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    // ⚠️ Trop de logique métier
    $abonnement = $user->ecole->abonnements()
        ->with('paiements.moyenPaiement')
        ->where('statut', 'actif')
        ->first();

    $montantRestant = $abonnement->calculateRemainingAmount();
    $echeances = $abonnement->getEcheancesNonPayees();

    // ...
}

// ✅ BON : Logique simple de vérification
public function handle(Request $request, Closure $next): Response
{
    if (!$request->user()->ecole->hasActiveSubscription()) {
        return response()->json(['message' => 'Abonnement expiré'], 403);
    }

    return $next($request);
}
```

---

#### 2. Ne pas dupliquer la logique entre middleware

```php
// ❌ MAUVAIS : Duplication
class EnsureEcoleHasActiveSubscription {
    // Vérifie l'abonnement
}

class CheckSubscriptionExpiry {
    // Vérifie aussi l'abonnement ⚠️ Duplication
}

// ✅ BON : Un seul middleware pour une responsabilité
class EnsureEcoleHasActiveSubscription {
    // Toute la logique d'abonnement ici
}
```

---

#### 3. Ne pas retourner des erreurs sans message clair

```php
// ❌ MAUVAIS : Message vague
return response()->json(['error' => 'Not allowed'], 403);

// ✅ BON : Message explicite
return response()->json([
    'success' => false,
    'message' => 'Votre abonnement a expiré le ' . $abonnement->date_fin->format('d/m/Y') . '. Veuillez renouveler.',
    'code' => 'SUBSCRIPTION_EXPIRED',
], 403);
```

---

## Résumé

### 🎯 Quand créer un Middleware ?

| Situation | Créer un Middleware ? |
|-----------|----------------------|
| Vérifier l'authentification | ✅ Oui (déjà fourni : `auth:api`) |
| Vérifier une permission | ✅ Oui (déjà fourni : `can:permission`) |
| Vérifier un statut métier (abonnement) | ✅ Oui |
| Logger les requêtes | ✅ Oui |
| Transformer les données | ✅ Oui |
| Logique métier complexe | ❌ Non (mettre dans Service) |

---

### 📋 Checklist Middleware

Avant de créer un middleware :

- [ ] Le middleware a **une seule responsabilité**
- [ ] Le nom est **descriptif** et clair
- [ ] Les réponses d'erreur sont en **JSON** cohérent
- [ ] Les codes HTTP sont **appropriés** (401, 403, 429, etc.)
- [ ] Les erreurs sont **loggées** si nécessaire
- [ ] Le middleware est **enregistré** dans Kernel.php
- [ ] Le middleware est **testé** (cas succès + échec)
- [ ] La documentation est **mise à jour**

---

## Prochaines étapes

📖 Consultez aussi :
- [ARCHITECTURE.md](ARCHITECTURE.md) - Principes SOLID
- [AUTHORIZATION.md](AUTHORIZATION.md) - Gates et Policies
- [CONTROLLERS_INJECTION.md](CONTROLLERS_INJECTION.md) - Injection de dépendances
- [BEST_PRACTICES.md](BEST_PRACTICES.md) - Bonnes pratiques
