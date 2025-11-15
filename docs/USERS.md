# Documentation - Système Users

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Modèle User](#modèle-user)
3. [Modèle UserInfo](#modèle-userinfo)
4. [Modèle OtpCode](#modèle-otpcode)
5. [Structure de la base de données](#structure-de-la-base-de-données)
6. [Relations](#relations)
7. [API Endpoints](#api-endpoints)
8. [Validation des données](#validation-des-données)
9. [Permissions](#permissions)
10. [Service Layer](#service-layer)
11. [Exemples d'utilisation](#exemples-dutilisation)

---

## 🎯 Vue d'ensemble

Le système Users est le cœur de l'authentification et de l'autorisation de l'application Sirene d'Ecole. Il gère :

- **Comptes utilisateurs** avec système polymorphique (Admin, Ecole, Technicien, User)
- **Authentification multi-méthodes** (identifiant/mot de passe, OTP)
- **Autorisation basée sur les rôles** (RBAC)
- **Informations utilisateur étendues** (UserInfo)
- **OTP pour connexion sécurisée**
- **Soft deletes** avec préservation des champs uniques

---

## 📦 Modèle User

**Fichier**: `app/Models/User.php`

### Caractéristiques principales

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUlid, SoftDeletes,
        HasApiTokens, SoftDeletesUniqueFields;
}
```

### Traits utilisés

| Trait | Description |
|-------|-------------|
| `HasFactory` | Support des factories pour les tests |
| `Notifiable` | Support des notifications Laravel |
| `HasUlid` | Clé primaire ULID (26 caractères) |
| `SoftDeletes` | Suppression douce (soft delete) |
| `HasApiTokens` | Tokens OAuth2 (Laravel Passport) |
| `SoftDeletesUniqueFields` | Préservation des champs uniques lors du soft delete |

### Champs de la table `users`

| Champ | Type | Description |
|-------|------|-------------|
| `id` | string(26) | Clé primaire ULID |
| `nom_utilisateur` | string(100) | Nom d'utilisateur (unique avec soft delete) |
| `identifiant` | string(100) | Identifiant de connexion (unique) |
| `mot_de_passe` | string | Mot de passe hashé |
| `doit_changer_mot_de_passe` | boolean | Flag pour forcer changement de mot de passe |
| `mot_de_passe_change` | boolean | Indique si le mot de passe a été changé |
| `type` | enum | Type de compte: ADMIN, TECHNICIEN, ECOLE, USER |
| `user_account_type_id` | string(26) | ID polymorphique du compte associé |
| `user_account_type_type` | string(100) | Type polymorphique (Ecole, Technicien, etc.) |
| `role_id` | string(26) | Clé étrangère vers `roles` |
| `actif` | boolean | Compte actif/inactif (défaut: false) |
| `statut` | integer | Statut du compte (-1, 0, 1) |
| `remember_token` | string | Token "Se souvenir de moi" |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de mise à jour |
| `deleted_at` | timestamp | Date de suppression (soft delete) |

### Champs fillable

```php
protected $fillable = [
    'nom_utilisateur',
    'identifiant',
    'mot_de_passe',
    'doit_changer_mot_de_passe',
    'mot_de_passe_change',
    'type',
    'user_account_type_id',
    'user_account_type_type',
    'role_id',
    'actif',
    'statut',
];
```

### Champs cachés

```php
protected $hidden = [
    'mot_de_passe',      // Mot de passe jamais retourné dans les réponses
    'remember_token',    // Token de session jamais retourné
];
```

### Casts

```php
protected $casts = [
    'actif' => 'boolean',
    'doit_changer_mot_de_passe' => 'boolean',
    'mot_de_passe_change' => 'boolean',
    'statut' => 'integer',
];
```

### Champs uniques avec soft delete

Lors d'un soft delete, ces champs sont modifiés pour éviter les conflits de contrainte unique :

```php
protected function getUniqueSoftDeleteFields(): array
{
    return ['nom_utilisateur', 'identifiant'];
}
```

**Comportement** : Quand un user est supprimé (soft delete), les champs `nom_utilisateur` et `identifiant` sont suffixés avec un timestamp pour permettre la réutilisation de ces valeurs.

Exemple : `john.doe` devient `john.doe_deleted_1699876543`

---

## 📝 Modèle UserInfo

**Fichier**: `app/Models/UserInfo.php`

Extension du modèle User pour stocker les informations personnelles détaillées.

### Champs de la table `user_infos`

| Champ | Type | Description |
|-------|------|-------------|
| `id` | string(26) | Clé primaire ULID |
| `user_id` | string(26) | Clé étrangère vers `users` |
| `nom` | string(255) | Nom de famille |
| `prenom` | string(255) | Prénom |
| `telephone` | string(20) | Numéro de téléphone (unique) |
| `email` | string(255) | Adresse email (unique, nullable) |
| `ville_id` | string(26) | Clé étrangère vers `villes` |
| `adresse` | string(255) | Adresse complète |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de mise à jour |
| `deleted_at` | timestamp | Date de suppression (soft delete) |

### Champs uniques avec soft delete

```php
protected function getUniqueSoftDeleteFields(): array
{
    return ['telephone', 'email'];
}
```

---

## 🔐 Modèle OtpCode

**Fichier**: `app/Models/OtpCode.php`

Gestion des codes OTP pour l'authentification à deux facteurs.

### Champs de la table `otp_codes`

| Champ | Type | Description |
|-------|------|-------------|
| `id` | string(26) | Clé primaire ULID |
| `user_id` | string(26) | Clé étrangère vers `users` |
| `code` | string | Code OTP (généralement 6 chiffres) |
| `telephone` | string | Numéro de téléphone destinataire |
| `type` | enum | Type d'OTP (TypeOtp enum) |
| `date_generation` | datetime | Date de génération du code |
| `expire_le` | datetime | Date d'expiration |
| `date_expiration` | datetime | Date d'expiration alternative |
| `utilise` | boolean | Code déjà utilisé |
| `valide` | boolean | Code validé |
| `est_verifie` | boolean | Code vérifié |
| `verifie` | boolean | Statut de vérification |
| `date_verification` | datetime | Date de vérification |
| `tentatives` | integer | Nombre de tentatives de vérification |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de mise à jour |
| `deleted_at` | timestamp | Date de suppression (soft delete) |

### Sécurité OTP

- **Expiration** : Codes temporaires avec date d'expiration
- **Limitation des tentatives** : Compteur `tentatives` pour éviter le brute force
- **Usage unique** : Flag `utilise` pour invalider après utilisation
- **Soft delete** : Historique conservé pour audit

---

## 🗄️ Structure de la base de données

### Migration principale

**Fichier**: `database/migrations/0001_01_01_000000_create_users_table.php`

```sql
CREATE TABLE users (
    id VARCHAR(26) PRIMARY KEY,                    -- ULID
    nom_utilisateur VARCHAR(100),
    identifiant VARCHAR(100) UNIQUE,
    mot_de_passe VARCHAR(255),
    type ENUM('ADMIN', 'TECHNICIEN', 'ECOLE', 'USER') DEFAULT 'USER',
    user_account_type_id VARCHAR(26),              -- Polymorphic ID
    user_account_type_type VARCHAR(100),           -- Polymorphic Type
    role_id VARCHAR(26),
    actif BOOLEAN DEFAULT FALSE,
    statut INTEGER DEFAULT -1,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,

    INDEX idx_user_account (user_account_type_id, user_account_type_type)
);
```

### Index de performance

- Index sur `identifiant` (unique)
- Index composite sur `user_account_type_id` + `user_account_type_type` (polymorphique)
- Index sur `deleted_at` (soft delete, automatique)

---

## 🔗 Relations

### 1. Relation polymorphique : userAccount()

```php
public function userAccount()
{
    return $this->morphTo('user_account_type');
}
```

**Description** : Un User peut être polymorphiquement lié à différents types de comptes :
- `Ecole` (établissement scolaire)
- `Technicien` (technicien de maintenance)
- `Admin` (administrateur système)

**Exemple** :
```php
$user = User::find($id);
$ecole = $user->userAccount; // Retourne une instance d'Ecole si type = ECOLE
```

### 2. Relation : role()

```php
public function role()
{
    return $this->belongsTo(Role::class);
}
```

**Description** : Un User appartient à un rôle pour la gestion des permissions (RBAC).

**Exemple** :
```php
$user = User::find($id);
$role = $user->role;
$permissions = $role->permissions;
```

### 3. Relation : userInfo()

```php
public function userInfo()
{
    return $this->hasOne(UserInfo::class);
}
```

**Description** : Un User a des informations personnelles étendues (nom, prénom, email, téléphone, adresse).

**Exemple** :
```php
$user = User::with('userInfo')->find($id);
echo $user->userInfo->prenom . ' ' . $user->userInfo->nom;
echo $user->userInfo->email;
```

### 4. Relation : otpCodes()

```php
public function otpCodes()
{
    return $this->hasMany(OtpCode::class);
}
```

**Description** : Un User peut avoir plusieurs codes OTP (historique des authentifications).

**Exemple** :
```php
$user = User::find($id);
$latestOtp = $user->otpCodes()->latest()->first();
```

### 5. Relation : notifications()

```php
public function notifications()
{
    return $this->morphMany(Notification::class, 'notifiable');
}
```

**Description** : Relation polymorphique pour les notifications Laravel.

---

## 🛣️ API Endpoints

### Base URL

```
/api/users
```

### Authentification requise

Tous les endpoints nécessitent le middleware `auth:api` (token OAuth2 Passport).

### Liste des endpoints

| Méthode | Endpoint | Action | Permission |
|---------|----------|--------|------------|
| GET | `/api/users` | Liste des utilisateurs (paginée) | `voir_les_utilisateurs` |
| GET | `/api/users/{id}` | Détails d'un utilisateur | `voir_utilisateur` |
| POST | `/api/users` | Créer un utilisateur | `creer_utilisateur` |
| PUT | `/api/users/{id}` | Modifier un utilisateur | `modifier_utilisateur` |
| DELETE | `/api/users/{id}` | Supprimer un utilisateur (soft delete) | `supprimer_utilisateur` |

### Détail des endpoints

#### 1. Liste des utilisateurs

```http
GET /api/users
Authorization: Bearer {token}
```

**Réponse** (200 OK) :
```json
{
  "success": true,
  "message": "Resources retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "01HJKM2VW3XYZ9ABCDEFGH1234",
        "nom_utilisateur": "john_doe",
        "identifiant": "john.doe@example.com",
        "type": "USER",
        "actif": true,
        "statut": 1,
        "role_id": "01HJKM2VW3XYZ9ABCDEFGH5678",
        "created_at": "2024-11-15T10:30:00.000000Z",
        "updated_at": "2024-11-15T10:30:00.000000Z",
        "user_info": {
          "id": "01HJKM2VW3XYZ9ABCDEFGH9012",
          "user_id": "01HJKM2VW3XYZ9ABCDEFGH1234",
          "nom": "Doe",
          "prenom": "John",
          "telephone": "+221771234567",
          "email": "john.doe@example.com",
          "ville_id": "01HJKM2VW3XYZ9ABCDEFGH3456",
          "adresse": "Dakar, Sénégal"
        }
      }
    ],
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

**Notes** :
- Pagination automatique (15 par page)
- Eager loading de `userInfo`

#### 2. Détails d'un utilisateur

```http
GET /api/users/{id}
Authorization: Bearer {token}
```

**Paramètres** :
- `{id}` : ULID de l'utilisateur

**Réponse** (200 OK) :
```json
{
  "success": true,
  "message": "Resource retrieved successfully",
  "data": {
    "id": "01HJKM2VW3XYZ9ABCDEFGH1234",
    "nom_utilisateur": "john_doe",
    "identifiant": "john.doe@example.com",
    "type": "ECOLE",
    "user_account_type_id": "01HJKM2VW3XYZ9ABCDEFGH7890",
    "user_account_type_type": "App\\Models\\Ecole",
    "role_id": "01HJKM2VW3XYZ9ABCDEFGH5678",
    "actif": true,
    "statut": 1,
    "doit_changer_mot_de_passe": false,
    "mot_de_passe_change": true,
    "created_at": "2024-11-15T10:30:00.000000Z",
    "updated_at": "2024-11-15T10:30:00.000000Z",
    "user_info": {
      "id": "01HJKM2VW3XYZ9ABCDEFGH9012",
      "nom": "Doe",
      "prenom": "John",
      "telephone": "+221771234567",
      "email": "john.doe@example.com",
      "ville_id": "01HJKM2VW3XYZ9ABCDEFGH3456",
      "adresse": "123 Avenue Example, Dakar"
    }
  }
}
```

#### 3. Créer un utilisateur

```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json
```

**Body** :
```json
{
  "role_id": "01HJKM2VW3XYZ9ABCDEFGH5678",
  "userInfoData": {
    "prenom": "John",
    "nom": "Doe",
    "telephone": "+221771234567",
    "email": "john.doe@example.com",
    "adresse": "123 Avenue Example, Dakar",
    "ville_id": "01HJKM2VW3XYZ9ABCDEFGH3456"
  }
}
```

**Réponse** (201 Created) :
```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": "01HJKM2VW3XYZ9ABCDEFGH1234",
    "nom_utilisateur": "john_doe",
    "identifiant": "auto_generated_identifier",
    "type": "USER",
    "role_id": "01HJKM2VW3XYZ9ABCDEFGH5678",
    "actif": false,
    "statut": -1,
    "doit_changer_mot_de_passe": true,
    "mot_de_passe_change": false,
    "created_at": "2024-11-15T10:30:00.000000Z",
    "updated_at": "2024-11-15T10:30:00.000000Z"
  }
}
```

#### 4. Modifier un utilisateur

```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Body** :
```json
{
  "nom_utilisateur": "johnny_doe",
  "role_id": "01HJKM2VW3XYZ9ABCDEFGH9999",
  "userInfoData": {
    "prenom": "Johnny",
    "nom": "Doe",
    "telephone": "+221771234567",
    "email": "johnny.doe@example.com",
    "adresse": "456 Boulevard Updated, Dakar",
    "ville_id": "01HJKM2VW3XYZ9ABCDEFGH3456"
  }
}
```

**Réponse** (200 OK) :
```json
{
  "success": true,
  "message": "Resource updated successfully",
  "data": {
    "id": "01HJKM2VW3XYZ9ABCDEFGH1234",
    "nom_utilisateur": "johnny_doe",
    "identifiant": "johnny.doe@example.com",
    "type": "USER",
    "role_id": "01HJKM2VW3XYZ9ABCDEFGH9999",
    "actif": true,
    "statut": 1,
    "updated_at": "2024-11-15T12:45:00.000000Z"
  }
}
```

#### 5. Supprimer un utilisateur

```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

**Réponse** (200 OK) :
```json
{
  "success": true,
  "message": "Resource deleted successfully",
  "data": null
}
```

**Notes** :
- Soft delete : l'enregistrement n'est pas physiquement supprimé
- Les champs uniques (`nom_utilisateur`, `identifiant`) sont suffixés avec timestamp
- Permet la récupération ultérieure si nécessaire

---

## ✅ Validation des données

### StoreUserRequest

**Fichier** : `app/Http/Requests/StoreUserRequest.php`

```php
public function rules(): array
{
    return [
        'role_id' => ['required', 'string', 'exists:roles,id'],

        // UserInfo data
        'userInfoData.email' => [
            'nullable', 'string', 'email', 'max:255',
            'unique:user_infos,email'
        ],
        'userInfoData.telephone' => [
            'required', 'string', 'max:20',
            'unique:user_infos,telephone'
        ],
        'userInfoData.prenom' => ['required', 'string', 'max:255'],
        'userInfoData.nom' => ['required', 'string', 'max:255'],
        'userInfoData.adresse' => ['nullable', 'string', 'max:255'],
        'userInfoData.ville_id' => ['nullable', 'string', 'exists:villes,id']
    ];
}
```

**Champs obligatoires** :
- `role_id` : Rôle de l'utilisateur
- `userInfoData.telephone` : Numéro de téléphone unique
- `userInfoData.prenom` : Prénom
- `userInfoData.nom` : Nom de famille

**Champs optionnels** :
- `userInfoData.email` : Email (unique si fourni)
- `userInfoData.adresse` : Adresse complète
- `userInfoData.ville_id` : Ville de résidence

### UpdateUserRequest

**Fichier** : `app/Http/Requests/UpdateUserRequest.php`

```php
public function rules(): array
{
    $userId = $this->route('id');

    return [
        'nom_utilisateur' => [
            'sometimes', 'string', 'max:255',
            Rule::unique('users', 'nom_utilisateur')->ignore($userId)
        ],
        'role_id' => ['sometimes', 'string', 'exists:roles,id'],

        'userInfoData.email' => [
            'sometimes', 'string', 'email', 'max:255',
            Rule::unique('user_infos', 'email')->ignore($userId, 'user_id')
        ],
        'userInfoData.telephone' => [
            'sometimes', 'string', 'max:20',
            Rule::unique('user_infos', 'telephone')->ignore($userId, 'user_id')
        ],
        'userInfoData.prenom' => ['nullable', 'string', 'max:255'],
        'userInfoData.nom' => ['nullable', 'string', 'max:255'],
        'userInfoData.adresse' => ['nullable', 'string', 'max:255'],
        'userInfoData.ville_id' => ['nullable', 'string', 'exists:villes,id']
    ];
}
```

**Notes** :
- Tous les champs sont optionnels (`sometimes`)
- Les validations `unique` ignorent l'enregistrement en cours de modification
- Validation des clés étrangères (`exists`)

---

## 🔒 Permissions

Toutes les actions sur les utilisateurs sont protégées par des permissions granulaires.

### Liste des permissions

| Permission | Description | Endpoints |
|------------|-------------|-----------|
| `voir_les_utilisateurs` | Voir la liste des utilisateurs | GET `/api/users` |
| `voir_utilisateur` | Voir les détails d'un utilisateur | GET `/api/users/{id}` |
| `creer_utilisateur` | Créer un nouvel utilisateur | POST `/api/users` |
| `modifier_utilisateur` | Modifier un utilisateur existant | PUT `/api/users/{id}` |
| `supprimer_utilisateur` | Supprimer un utilisateur | DELETE `/api/users/{id}` |

### Middleware appliqué

```php
// Dans UserController.php
$this->middleware('can:voir_les_utilisateurs')->only('index');
$this->middleware('can:voir_utilisateur')->only('show');
$this->middleware('can:creer_utilisateur')->only('store');
$this->middleware('can:modifier_utilisateur')->only('update');
$this->middleware('can:supprimer_utilisateur')->only('destroy');
```

### Vérification dans le contrôleur

```php
public function index()
{
    Gate::authorize('voir_les_utilisateurs');
    return $this->userService->getAll(15, relations: ["userInfo"]);
}
```

**Double vérification** :
1. Middleware : bloque l'accès avant l'exécution de la méthode
2. Gate::authorize : vérification explicite dans la méthode

---

## 🛠️ Service Layer

**Fichier** : `app/Services/UserService.php`

Le UserService encapsule la logique métier pour les utilisateurs.

### Méthodes principales

#### create(array $data): JsonResponse

Crée un nouvel utilisateur avec transaction DB.

```php
public function create(array $data): JsonResponse
{
    try {
        DB::beginTransaction();
        $model = $this->repository->create($data);
        DB::commit();
        return $this->createdResponse($model);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error in UserService::create - " . $e->getMessage());
        return $this->errorResponse($e->getMessage(), 500);
    }
}
```

#### update(string $id, array $data): JsonResponse

Met à jour un utilisateur existant avec transaction DB.

```php
public function update(string $id, array $data): JsonResponse
{
    try {
        DB::beginTransaction();
        $this->repository->update($id, $data);
        DB::commit();
        $model = $this->repository->find($id);
        return $this->successResponse(null, $model);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error in UserService::update - " . $e->getMessage());
        return $this->errorResponse($e->getMessage(), 500);
    }
}
```

#### findByIdentifier(string $identifier): JsonResponse

Recherche un utilisateur par son identifiant.

```php
public function findByIdentifier(string $identifier): JsonResponse
{
    try {
        $user = $this->repository->findByIdentifier($identifier);
        if (!$user) {
            return $this->notFoundResponse();
        }
        return $this->successResponse(null, $user);
    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}
```

#### findByEmail(string $email): JsonResponse

Recherche un utilisateur par email (via UserInfo).

#### findByPhone(string $phone): JsonResponse

Recherche un utilisateur par téléphone (via UserInfo).

#### findByUserAccount(string $accountType, string $accountId): JsonResponse

Recherche un utilisateur par son compte polymorphique.

```php
// Exemple : trouver l'utilisateur d'une école
$user = $userService->findByUserAccount('App\Models\Ecole', $ecoleId);
```

#### userExists(string $identifier): JsonResponse

Vérifie si un utilisateur existe.

```php
// Réponse
{
  "success": true,
  "data": {
    "exists": true
  }
}
```

#### updateStatus(string $id, int $status): JsonResponse

Met à jour le statut d'un utilisateur (-1, 0, 1).

#### activateUser(string $id): JsonResponse

Active un compte utilisateur (`actif = true`).

#### deactivateUser(string $id): JsonResponse

Désactive un compte utilisateur (`actif = false`).

#### updatePassword(string $id, string $newPassword): JsonResponse

Met à jour le mot de passe d'un utilisateur.

---

## 📚 Exemples d'utilisation

### 1. Créer un utilisateur Ecole

```php
use App\Models\User;
use App\Models\Ecole;
use App\Models\Role;

// Créer l'école
$ecole = Ecole::create([
    'nom_etablissement' => 'Lycée Jean-Baptiste',
    'code_etablissement' => 'ECOLE-123456',
    // ... autres champs
]);

// Créer l'utilisateur lié à l'école
$user = User::create([
    'nom_utilisateur' => 'lycee_jb',
    'identifiant' => 'contact@lyceejb.edu',
    'mot_de_passe' => bcrypt('SecurePassword123'),
    'type' => 'ECOLE',
    'user_account_type_id' => $ecole->id,
    'user_account_type_type' => Ecole::class,
    'role_id' => Role::where('slug', 'ecole')->first()->id,
    'actif' => true,
    'statut' => 1,
]);

// Créer les informations utilisateur
$user->userInfo()->create([
    'nom' => 'Administration',
    'prenom' => 'Lycée Jean-Baptiste',
    'telephone' => '+221771234567',
    'email' => 'contact@lyceejb.edu',
    'adresse' => 'Avenue Blaise Diagne, Dakar',
    'ville_id' => $villeId,
]);
```

### 2. Authentification par identifiant

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$identifiant = 'contact@lyceejb.edu';
$password = 'SecurePassword123';

$user = User::where('identifiant', $identifiant)->first();

if ($user && Hash::check($password, $user->mot_de_passe)) {
    // Créer token Passport
    $token = $user->createToken('api-token')->accessToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user->load('userInfo', 'role'),
    ]);
}

return response()->json([
    'success' => false,
    'message' => 'Identifiants incorrects',
], 401);
```

### 3. Vérifier les permissions d'un utilisateur

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;

$user = User::find($userId);

// Via Gate
if (Gate::forUser($user)->allows('creer_utilisateur')) {
    // L'utilisateur peut créer des utilisateurs
}

// Via le modèle User (si vous ajoutez une méthode hasPermission)
if ($user->role->permissions->contains('slug', 'creer_utilisateur')) {
    // L'utilisateur a la permission
}
```

### 4. Récupérer le compte polymorphique

```php
use App\Models\User;

$user = User::with('userAccount')->find($userId);

// Si le user est de type ECOLE
if ($user->type === 'ECOLE') {
    $ecole = $user->userAccount; // Instance de Ecole
    echo $ecole->nom_etablissement;
}

// Si le user est de type TECHNICIEN
if ($user->type === 'TECHNICIEN') {
    $technicien = $user->userAccount; // Instance de Technicien
    echo $technicien->specialite;
}
```

### 5. Générer et vérifier un OTP

```php
use App\Models\User;
use App\Models\OtpCode;
use Carbon\Carbon;

// Générer un OTP
$user = User::where('identifiant', $telephone)->first();

$otp = OtpCode::create([
    'user_id' => $user->id,
    'code' => rand(100000, 999999), // 6 chiffres
    'telephone' => $telephone,
    'type' => TypeOtp::CONNEXION,
    'date_generation' => now(),
    'expire_le' => now()->addMinutes(5),
    'utilise' => false,
    'valide' => false,
    'tentatives' => 0,
]);

// Envoyer le code par SMS...

// Vérifier l'OTP
$inputCode = '123456';
$otp = OtpCode::where('user_id', $user->id)
    ->where('code', $inputCode)
    ->where('utilise', false)
    ->where('expire_le', '>', now())
    ->first();

if ($otp) {
    if ($otp->tentatives >= 3) {
        return response()->json(['error' => 'Trop de tentatives'], 429);
    }

    $otp->update([
        'utilise' => true,
        'valide' => true,
        'est_verifie' => true,
        'verifie' => true,
        'date_verification' => now(),
    ]);

    // Créer token d'authentification
    $token = $user->createToken('api-token')->accessToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => $user,
    ]);
} else {
    // Incrémenter les tentatives
    if ($otp) {
        $otp->increment('tentatives');
    }

    return response()->json(['error' => 'Code invalide ou expiré'], 401);
}
```

### 6. Forcer le changement de mot de passe

```php
use App\Models\User;

$user = User::find($userId);

// Marquer que l'utilisateur doit changer son mot de passe
$user->update([
    'doit_changer_mot_de_passe' => true,
    'mot_de_passe_change' => false,
]);

// Lors de la prochaine connexion, rediriger vers le formulaire de changement de mot de passe
```

### 7. Soft delete et récupération

```php
use App\Models\User;

$user = User::find($userId);

// Soft delete
$user->delete();
// Les champs uniques sont automatiquement suffixés :
// nom_utilisateur: "john_doe" → "john_doe_deleted_1699876543"
// identifiant: "john@example.com" → "john@example.com_deleted_1699876543"

// Récupérer les utilisateurs supprimés
$deletedUsers = User::onlyTrashed()->get();

// Restaurer un utilisateur
$user = User::withTrashed()->find($userId);
$user->restore();
// Les champs uniques sont restaurés à leur valeur originale

// Suppression définitive (hard delete)
$user->forceDelete();
```

### 8. Requêtes avec relations

```php
use App\Models\User;

// Charger les relations
$user = User::with(['userInfo', 'role', 'userAccount', 'otpCodes'])
    ->find($userId);

// Eager loading conditionnel
$users = User::with([
    'userInfo',
    'role.permissions',
    'userAccount' => function ($query) {
        // Personnaliser la requête du compte polymorphique
    }
])->get();

// Compter les OTP actifs
$user = User::withCount(['otpCodes' => function ($query) {
    $query->where('utilise', false)
          ->where('expire_le', '>', now());
}])->find($userId);

echo $user->otp_codes_count; // Nombre d'OTP actifs
```

---

## 🔧 Configuration

### Variables d'environnement

```env
# Passport OAuth2
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=client-id-here
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=client-secret-here

# OTP Configuration
OTP_EXPIRATION_MINUTES=5
OTP_MAX_ATTEMPTS=3
OTP_LENGTH=6

# SMS Provider (pour envoi OTP)
SMS_PROVIDER=your-sms-provider
SMS_API_KEY=your-api-key
```

### Seeders

Pour créer des utilisateurs de test, utiliser les seeders :

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

---

## 🚨 Sécurité

### Bonnes pratiques implémentées

1. **Hashage des mots de passe** : Utilisation de `bcrypt()` (Bcrypt avec cost factor 10)
2. **Tokens sécurisés** : OAuth2 via Laravel Passport
3. **Soft delete** : Récupération possible en cas d'erreur
4. **Champs cachés** : `mot_de_passe` et `remember_token` jamais retournés
5. **Validation stricte** : Règles de validation sur toutes les entrées
6. **Permissions granulaires** : RBAC avec vérification double (middleware + Gate)
7. **OTP avec expiration** : Codes temporaires pour authentification sécurisée
8. **Limitation des tentatives** : Protection contre le brute force
9. **Logs** : Toutes les erreurs sont loguées pour audit
10. **Transactions DB** : Rollback automatique en cas d'erreur

### Recommandations

- Toujours utiliser HTTPS en production
- Configurer le rate limiting sur les endpoints sensibles
- Activer le 2FA (OTP) pour les comptes sensibles
- Auditer régulièrement les logs d'authentification
- Mettre en place une politique de mots de passe forts
- Révoquer les tokens lors de la déconnexion

---

## 📊 Diagramme relationnel

```
┌─────────────────┐         ┌─────────────────┐
│     Users       │─────────│   UserInfo      │
│                 │ 1     1 │                 │
│ - id (ULID)     │         │ - id (ULID)     │
│ - nom_utilisateur│        │ - user_id (FK)  │
│ - identifiant   │         │ - nom           │
│ - mot_de_passe  │         │ - prenom        │
│ - type          │         │ - telephone     │
│ - role_id (FK)  │         │ - email         │
│ - actif         │         │ - ville_id (FK) │
│ - statut        │         │ - adresse       │
└────────┬────────┘         └─────────────────┘
         │
         │ 1:N
         │
┌────────▼────────┐         ┌─────────────────┐
│   OtpCodes      │         │     Roles       │
│                 │         │                 │
│ - id (ULID)     │         │ - id (ULID)     │
│ - user_id (FK)  │     ┌───│ - nom           │
│ - code          │     │   │ - slug          │
│ - telephone     │     │   │ - description   │
│ - type          │     │   └─────────────────┘
│ - expire_le     │     │
│ - utilise       │     │ N:N
│ - tentatives    │     │   ┌─────────────────┐
└─────────────────┘     └───│  Permissions    │
                            │                 │
         │                  │ - id (ULID)     │
         │ polymorphic      │ - nom           │
         │                  │ - slug          │
┌────────▼────────┐         │ - description   │
│  Ecole/Technicien│        └─────────────────┘
│  (userAccount)  │
│                 │
│ - id (ULID)     │
└─────────────────┘
```

---

## 📞 Support

Pour toute question ou problème concernant le système Users :

1. Consulter cette documentation
2. Vérifier les logs : `storage/logs/laravel.log`
3. Consulter le code source : `app/Models/User.php`, `app/Http/Controllers/Api/UserController.php`
4. Contacter l'équipe de développement

---

**Version** : 1.0.0
**Dernière mise à jour** : 15 novembre 2024
**Auteur** : Équipe Backend Sirene d'Ecole
