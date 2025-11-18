# Guide d'Autorisation - Form Requests, Gates & Policies

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Méthode authorize() dans Form Requests](#méthode-authorize-dans-form-requests)
3. [Gates (Portes d'accès)](#gates-portes-daccès)
4. [Policies (Politiques)](#policies-politiques)
5. [Middleware d'autorisation](#middleware-dautorisation)
6. [RBAC - Role-Based Access Control](#rbac---role-based-access-control)
7. [Exemples concrets du projet](#exemples-concrets-du-projet)
8. [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

L'**autorisation** détermine si un utilisateur a le **droit** d'effectuer une action spécifique.

### Différence entre Authentification et Autorisation

| Authentification | Autorisation |
|-----------------|--------------|
| **Qui êtes-vous ?** | **Que pouvez-vous faire ?** |
| Login, mot de passe, token | Permissions, rôles |
| `auth:api` middleware | `can:permission` middleware |
| Vérifie l'identité | Vérifie les droits |

---

## Méthode authorize() dans Form Requests

### 🎯 Rôle de la méthode authorize()

Dans chaque `FormRequest`, la méthode `authorize()` détermine si l'utilisateur peut effectuer cette requête.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEcoleRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        // Retourner true si autorisé, false sinon
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            // ...
        ];
    }
}
```

### ❌ Si authorize() retourne false

Laravel retourne automatiquement une erreur **403 Forbidden** :

```json
{
  "message": "This action is unauthorized."
}
```

---

## Stratégies d'autorisation dans Form Requests

### 1️⃣ Autoriser tout le monde

**Cas d'usage :** Routes publiques (inscription, login)

```php
class InscriptionEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ✅ Tout le monde peut s'inscrire
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            'email' => 'required|email|unique:ecoles',
            // ...
        ];
    }
}
```

---

### 2️⃣ Autoriser seulement les utilisateurs authentifiés

**Cas d'usage :** Routes protégées nécessitant un login

```php
class CreateSireneRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ L'utilisateur doit être authentifié
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'numero_serie' => 'required|string',
            'modele_id' => 'required|exists:modeles_sirenes,id',
            // ...
        ];
    }
}
```

---

### 3️⃣ Autoriser selon une permission spécifique

**Cas d'usage :** RBAC (Role-Based Access Control)

```php
class CreateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ L'utilisateur doit avoir la permission 'create-ecole'
        return $this->user()?->can('create-ecole') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            // ...
        ];
    }
}
```

**Explication :**
- `$this->user()` : Récupère l'utilisateur authentifié
- `?->can('create-ecole')` : Vérifie si l'utilisateur a la permission
- `?? false` : Retourne `false` si l'utilisateur n'est pas authentifié

---

### 4️⃣ Autoriser selon le rôle

**Cas d'usage :** Vérifier si l'utilisateur a un rôle spécifique

```php
class CreateTechnicienRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ✅ Seuls les administrateurs peuvent créer des techniciens
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            // ...
        ];
    }
}
```

---

### 5️⃣ Autoriser selon l'ownership (propriétaire)

**Cas d'usage :** Un utilisateur ne peut modifier que SES propres ressources

```php
class UpdateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ecole = $this->route('id'); // ID de l'école dans l'URL

        // ✅ L'utilisateur doit être l'école qu'il essaie de modifier
        // OU avoir la permission 'update-any-ecole'
        return $this->user()?->id === $ecole
            || $this->user()?->can('update-any-ecole');
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string',
            'telephone' => 'sometimes|string',
            // ...
        ];
    }
}
```

---

### 6️⃣ Autorisation complexe avec logique métier

**Cas d'usage :** Vérifier des conditions métier avant autorisation

```php
class RenouvellerAbonnementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $abonnement = Abonnement::find($this->route('id'));

        if (!$abonnement) {
            return false; // ❌ Abonnement inexistant
        }

        // ✅ L'utilisateur doit :
        // 1. Être l'école propriétaire de l'abonnement
        // 2. OU avoir la permission 'manage-abonnements'
        $isOwner = $this->user()?->id === $abonnement->ecole_id;
        $hasPermission = $this->user()?->can('manage-abonnements');

        return $isOwner || $hasPermission;
    }

    public function rules(): array
    {
        return [
            'moyen_paiement' => 'required|string',
            // ...
        ];
    }
}
```

---

## Gates (Portes d'accès)

### 🎯 Qu'est-ce qu'un Gate ?

Un **Gate** est une fonction simple qui détermine si un utilisateur peut effectuer une action.

### 📁 Où définir les Gates ?

**Fichier : `app/Providers/AuthServiceProvider.php`**

```php
<?php

namespace App\Providers;

use App\Models\Ecole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Gate simple : vérifier une permission
        Gate::define('create-ecole', function (User $user) {
            return $user->hasPermission('create-ecole');
        });

        // Gate complexe : vérifier ownership
        Gate::define('update-ecole', function (User $user, Ecole $ecole) {
            // L'utilisateur peut mettre à jour si :
            // 1. C'est son école
            // 2. OU il a la permission 'update-any-ecole'
            return $user->id === $ecole->id
                || $user->hasPermission('update-any-ecole');
        });

        // Gate pour les admins uniquement
        Gate::define('manage-users', function (User $user) {
            return $user->hasRole('admin');
        });

        // Gate avec logique métier
        Gate::define('renew-abonnement', function (User $user, Abonnement $abonnement) {
            // Vérifier que l'abonnement expire dans moins de 30 jours
            $canRenew = $abonnement->date_fin->diffInDays(now()) <= 30;

            // ET que l'utilisateur est le propriétaire
            $isOwner = $user->id === $abonnement->ecole_id;

            return $canRenew && $isOwner;
        });
    }
}
```

### 🔧 Utilisation des Gates

#### Dans un FormRequest

```php
class UpdateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ecole = Ecole::find($this->route('id'));

        // ✅ Utiliser le Gate défini
        return Gate::allows('update-ecole', $ecole);

        // OU avec la méthode can()
        return $this->user()?->can('update-ecole', $ecole) ?? false;
    }
}
```

#### Dans un Controller

```php
class EcoleController extends Controller
{
    public function update(UpdateEcoleRequest $request, string $id)
    {
        $ecole = Ecole::findOrFail($id);

        // ✅ Vérifier l'autorisation manuellement
        if (!Gate::allows('update-ecole', $ecole)) {
            abort(403, 'Action non autorisée');
        }

        // OU utiliser authorize() qui lance automatiquement 403
        $this->authorize('update-ecole', $ecole);

        // Mettre à jour...
    }
}
```

#### Dans un Middleware de route

```php
// routes/api.php
Route::put('/ecoles/{id}', [EcoleController::class, 'update'])
    ->middleware('can:update-ecole,id');
```

---

## Policies (Politiques)

### 🎯 Qu'est-ce qu'une Policy ?

Une **Policy** est une classe dédiée qui regroupe toutes les autorisations pour un modèle spécifique.

**👍 Avantage :** Mieux organisé que les Gates pour des modèles complexes

### 📝 Créer une Policy

```bash
php artisan make:policy EcolePolicy --model=Ecole
```

**Fichier : `app/Policies/EcolePolicy.php`**

```php
<?php

namespace App\Policies;

use App\Models\Ecole;
use App\Models\User;

class EcolePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir toutes les écoles
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-ecoles');
    }

    /**
     * Déterminer si l'utilisateur peut voir une école
     */
    public function view(User $user, Ecole $ecole): bool
    {
        // L'utilisateur peut voir :
        // 1. Sa propre école
        // 2. OU s'il a la permission 'view-any-ecole'
        return $user->id === $ecole->id
            || $user->hasPermission('view-any-ecole');
    }

    /**
     * Déterminer si l'utilisateur peut créer une école
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-ecole');
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour une école
     */
    public function update(User $user, Ecole $ecole): bool
    {
        return $user->id === $ecole->id
            || $user->hasPermission('update-any-ecole');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer une école
     */
    public function delete(User $user, Ecole $ecole): bool
    {
        // Seuls les admins peuvent supprimer
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut restaurer une école supprimée
     */
    public function restore(User $user, Ecole $ecole): bool
    {
        return $user->hasRole('admin');
    }
}
```

### 📋 Enregistrer la Policy

**Fichier : `app/Providers/AuthServiceProvider.php`**

```php
<?php

namespace App\Providers;

use App\Models\Ecole;
use App\Policies\EcolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Les politiques de l'application
     */
    protected $policies = [
        Ecole::class => EcolePolicy::class,
    ];

    public function boot(): void
    {
        // Enregistrer les politiques
        $this->registerPolicies();
    }
}
```

### 🔧 Utilisation des Policies

#### Dans un FormRequest

```php
class UpdateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ecole = Ecole::find($this->route('id'));

        // ✅ Laravel trouve automatiquement la Policy
        return $this->user()?->can('update', $ecole) ?? false;
    }
}
```

#### Dans un Controller

```php
class EcoleController extends Controller
{
    public function update(UpdateEcoleRequest $request, string $id)
    {
        $ecole = Ecole::findOrFail($id);

        // ✅ Vérifier avec la Policy
        $this->authorize('update', $ecole);

        // Mettre à jour...
    }
}
```

#### Dans un Middleware de route

```php
// routes/api.php
Route::put('/ecoles/{id}', [EcoleController::class, 'update'])
    ->middleware('can:update,id');
```

#### Dans Blade (si vous avez des vues)

```blade
@can('update', $ecole)
    <button>Modifier</button>
@endcan

@cannot('delete', $ecole)
    <p>Vous ne pouvez pas supprimer cette école</p>
@endcannot
```

---

## Middleware d'autorisation

### 🛡️ Middleware can

**Syntaxe :** `can:permission` ou `can:action,model`

```php
// routes/api.php

// Simple permission
Route::post('/ecoles', [EcoleController::class, 'store'])
    ->middleware('can:create-ecole');

// Policy avec modèle
Route::put('/ecoles/{id}', [EcoleController::class, 'update'])
    ->middleware('can:update,id'); // Laravel injecte automatiquement Ecole

Route::delete('/ecoles/{id}', [EcoleController::class, 'destroy'])
    ->middleware('can:delete,id');
```

### 🔐 Middleware role

**Créer un middleware personnalisé pour les rôles :**

```bash
php artisan make:middleware CheckRole
```

**Fichier : `app/Http/Middleware/CheckRole.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès interdit. Rôle requis : ' . $role,
            ], 403);
        }

        return $next($request);
    }
}
```

**Enregistrer dans `app/Http/Kernel.php` :**

```php
protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

**Utilisation :**

```php
// routes/api.php
Route::post('/users', [UserController::class, 'store'])
    ->middleware('role:admin');

Route::post('/techniciens', [TechnicienController::class, 'store'])
    ->middleware('role:admin');
```

---

## RBAC - Role-Based Access Control

### 🎯 Système de Rôles et Permissions du projet

Le projet utilise un système **RBAC** complet avec :

```
User ──→ Roles ──→ Permissions
```

### 📦 Structure des tables

```
users
├── id
├── email
└── type (admin, ecole, technicien)

roles
├── id
├── name (admin, manager, viewer)
└── description

permissions
├── id
├── name (create-ecole, update-ecole, delete-ecole)
├── description
└── module (ecoles, sirenes, abonnements)

role_permissions (pivot)
├── role_id
└── permission_id

user_roles (si vous utilisez plusieurs rôles par user)
├── user_id
└── role_id
```

### 🔧 Méthodes utiles dans le modèle User

**Fichier : `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * Vérifier si l'utilisateur a un rôle
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * Vérifier si l'utilisateur a toutes les permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Vérifier si l'utilisateur a au moins une permission
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Relation : Rôles de l'utilisateur
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}
```

---

## Exemples concrets du projet

### Exemple 1 : CreateEcoleRequest (Inscription publique)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InscriptionEcoleRequest extends FormRequest
{
    /**
     * ✅ Autoriser tout le monde (route publique)
     */
    public function authorize(): bool
    {
        return true; // Inscription ouverte à tous
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:ecoles,email',
            'telephone' => 'required|string',
            'ville_id' => 'required|exists:villes,id',
        ];
    }
}
```

---

### Exemple 2 : UpdateEcoleRequest (Modification école)

```php
<?php

namespace App\Http\Requests;

use App\Models\Ecole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEcoleRequest extends FormRequest
{
    /**
     * ✅ Autoriser seulement le propriétaire ou admin
     */
    public function authorize(): bool
    {
        $ecoleId = $this->route('id');
        $user = $this->user();

        if (!$user) {
            return false; // ❌ Pas authentifié
        }

        // ✅ L'utilisateur peut modifier si :
        // 1. C'est son école
        // 2. OU il a la permission 'update-any-ecole'
        return $user->id === $ecoleId
            || $user->hasPermission('update-any-ecole');
    }

    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string',
            'adresse' => 'sometimes|string',
        ];
    }
}
```

---

### Exemple 3 : CreateTechnicienRequest (Admin seulement)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTechnicienRequest extends FormRequest
{
    /**
     * ✅ Seuls les admins peuvent créer des techniciens
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ];
    }
}
```

---

### Exemple 4 : DeclarerPanneRequest (École propriétaire)

```php
<?php

namespace App\Http\Requests;

use App\Models\Sirene;
use Illuminate\Foundation\Http\FormRequest;

class DeclarerPanneRequest extends FormRequest
{
    /**
     * ✅ L'école doit être propriétaire de la sirène
     */
    public function authorize(): bool
    {
        $sireneId = $this->route('id');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Récupérer la sirène
        $sirene = Sirene::find($sireneId);

        if (!$sirene) {
            return false;
        }

        // ✅ Vérifier que l'utilisateur est l'école propriétaire
        return $user->id === $sirene->ecole_id;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'priorite' => 'required|in:basse,normale,haute,urgente',
        ];
    }
}
```

---

### Exemple 5 : RenouvellerAbonnementRequest (Avec vérification métier)

```php
<?php

namespace App\Http\Requests;

use App\Enums\StatutAbonnement;
use App\Models\Abonnement;
use Illuminate\Foundation\Http\FormRequest;

class RenouvellerAbonnementRequest extends FormRequest
{
    /**
     * ✅ Autorisation avec logique métier
     */
    public function authorize(): bool
    {
        $abonnementId = $this->route('id');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $abonnement = Abonnement::find($abonnementId);

        if (!$abonnement) {
            return false;
        }

        // Vérifications :
        // 1. L'utilisateur est le propriétaire
        $isOwner = $user->id === $abonnement->ecole_id;

        // 2. L'abonnement n'est pas déjà actif avec une date future
        $canRenew = $abonnement->statut !== StatutAbonnement::ACTIF
            || $abonnement->date_fin <= now()->addDays(30);

        // ✅ Autoriser si propriétaire ET peut renouveler
        return $isOwner && $canRenew;
    }

    public function rules(): array
    {
        return [
            'moyen_paiement' => 'required|string',
            'montant' => 'sometimes|numeric|min:0',
        ];
    }
}
```

---

## Bonnes pratiques

### ✅ À FAIRE

#### 1. Toujours définir authorize()

```php
// ✅ BON
class CreateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-ecole') ?? false;
    }
}

// ❌ MAUVAIS : Laisser le défaut (retourne false)
class CreateEcoleRequest extends FormRequest
{
    // Pas de méthode authorize() définie → Retourne false par défaut
}
```

---

#### 2. Utiliser les Policies pour les modèles complexes

```php
// ✅ BON : Policy bien organisée
class EcolePolicy
{
    public function view(User $user, Ecole $ecole): bool { }
    public function create(User $user): bool { }
    public function update(User $user, Ecole $ecole): bool { }
    public function delete(User $user, Ecole $ecole): bool { }
}

// ❌ MAUVAIS : Tout dans des Gates éparpillés
Gate::define('view-ecole', function ($user, $ecole) { });
Gate::define('create-ecole', function ($user) { });
Gate::define('update-ecole', function ($user, $ecole) { });
// ... difficile à maintenir
```

---

#### 3. Centraliser les vérifications de permissions

```php
// ✅ BON : Méthode dans le modèle User
class User extends Authenticatable
{
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }
}

// Utilisation simple
if ($user->hasPermission('create-ecole')) { }

// ❌ MAUVAIS : Requête SQL partout
if (DB::table('role_permissions')
    ->join('user_roles', ...)
    ->where('permission_name', 'create-ecole')
    ->exists()) { }
```

---

#### 4. Retourner des messages d'erreur clairs

```php
// ✅ BON : Message personnalisé
class UpdateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ...
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException(
            'Vous n\'avez pas le droit de modifier cette école.'
        );
    }
}

// ❌ MAUVAIS : Message générique Laravel
// "This action is unauthorized." (pas clair)
```

---

#### 5. Combiner authorize() avec middleware

```php
// ✅ BON : Double vérification
// Route
Route::put('/ecoles/{id}', [EcoleController::class, 'update'])
    ->middleware('can:update,id');

// FormRequest
class UpdateEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ecole = Ecole::find($this->route('id'));
        return $this->user()?->can('update', $ecole) ?? false;
    }
}
```

---

### ❌ À ÉVITER

#### 1. Ne pas mettre de logique métier dans authorize()

```php
// ❌ MAUVAIS : Logique métier dans authorize()
public function authorize(): bool
{
    $ecole = Ecole::find($this->route('id'));

    // ⚠️ Ceci est de la logique métier, pas de l'autorisation !
    if ($ecole->abonnements()->where('statut', 'actif')->count() >= 5) {
        return false;
    }

    return $this->user()->id === $ecole->id;
}

// ✅ BON : Logique métier dans le Service
public function authorize(): bool
{
    // Seulement vérifier les permissions
    return $this->user()?->can('update', Ecole::find($this->route('id')));
}

// Service
public function update(string $id, array $data): Ecole
{
    // ✅ Logique métier ici
    $ecole = $this->ecoleRepository->find($id);

    if ($ecole->abonnements()->where('statut', 'actif')->count() >= 5) {
        throw new BusinessException('Limite d\'abonnements atteinte.');
    }

    return $this->ecoleRepository->update($id, $data);
}
```

---

#### 2. Ne pas faire de requêtes lourdes dans authorize()

```php
// ❌ MAUVAIS : Requête lourde
public function authorize(): bool
{
    return Ecole::with('abonnements.paiements')
        ->withCount('sirenes', 'techniciens')
        ->find($this->route('id'))
        ->user_id === $this->user()->id;
}

// ✅ BON : Requête simple
public function authorize(): bool
{
    $ecole = Ecole::select('id', 'user_id')->find($this->route('id'));
    return $ecole?->user_id === $this->user()?->id;
}
```

---

#### 3. Ne pas ignorer les utilisateurs non authentifiés

```php
// ❌ MAUVAIS : Peut générer une erreur si $user est null
public function authorize(): bool
{
    return $this->user()->hasPermission('create-ecole'); // ⚠️ Erreur si null
}

// ✅ BON : Gérer le cas null
public function authorize(): bool
{
    return $this->user()?->hasPermission('create-ecole') ?? false;
}
```

---

## Résumé

### 🎯 Quand utiliser quoi ?

| Cas d'usage | Solution | Exemple |
|-------------|----------|---------|
| Route publique | `return true;` | Inscription, Login |
| Route authentifiée simple | `$this->user() !== null` | Lister ses propres données |
| Permission simple | `$user->hasPermission('name')` | Créer une école |
| Vérification ownership | Policy ou Gate | Modifier SES données |
| Logique complexe | Policy | Plusieurs conditions |
| Protection globale de route | Middleware | `->middleware('can:action')` |

---

### 📋 Checklist d'autorisation

Avant de déployer une API, vérifiez :

- [ ] Toutes les FormRequests ont une méthode `authorize()` définie
- [ ] Les routes publiques retournent `true`
- [ ] Les routes protégées vérifient l'authentification
- [ ] Les permissions sont vérifiées via RBAC
- [ ] Les Policies sont enregistrées dans `AuthServiceProvider`
- [ ] Les middleware d'autorisation sont appliqués sur les routes sensibles
- [ ] Les messages d'erreur 403 sont clairs
- [ ] Les tests vérifient les autorisations (200 vs 403)

---

## Prochaines étapes

📖 Consultez aussi :
- [ARCHITECTURE.md](ARCHITECTURE.md) - Principes SOLID
- [DEV_GUIDE.md](DEV_GUIDE.md) - Guide de développement
- [BEST_PRACTICES.md](BEST_PRACTICES.md) - Bonnes pratiques
- [FAQ.md](FAQ.md) - Questions fréquentes
