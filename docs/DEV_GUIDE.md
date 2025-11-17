# Guide de Développement API REST

## 📚 Table des matières

1. [Introduction](#introduction)
2. [Checklist de développement](#checklist-de-développement)
3. [Guide étape par étape](#guide-étape-par-étape)
4. [Composants détaillés](#composants-détaillés)
5. [Gestion des erreurs](#gestion-des-erreurs)
6. [Tests](#tests)

---

## Introduction

Ce guide vous accompagne **pas à pas** dans la création d'une nouvelle fonctionnalité API REST en suivant l'architecture SOLID du projet.

### Pré-requis

- Avoir lu le document [ARCHITECTURE.md](ARCHITECTURE.md)
- Comprendre les principes SOLID
- Connaître Laravel et Eloquent

---

## Checklist de développement

Lorsque vous développez une nouvelle fonctionnalité API, suivez cette checklist :

```
☐ 1. Créer la migration de base de données
☐ 2. Créer le modèle Eloquent
☐ 3. Créer l'Enum (si nécessaire)
☐ 4. Créer l'interface du Repository
☐ 5. Créer l'implémentation du Repository
☐ 6. Créer l'interface du Service
☐ 7. Créer l'implémentation du Service
☐ 8. Enregistrer dans le ServiceProvider
☐ 9. Créer les Form Requests
☐ 10. Créer l'API Resource
☐ 11. Créer le Controller
☐ 12. Définir les routes
☐ 13. Ajouter les permissions (RBAC)
☐ 14. Tester l'API
☐ 15. Documenter avec Swagger
```

---

## Guide étape par étape

Prenons un exemple concret : créer une API pour gérer les **Départements** (`Department`).

### Étape 1 : Créer la migration

```bash
php artisan make:migration create_departments_table
```

**Fichier : `database/migrations/2024_xx_xx_create_departments_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nom');
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignUlid('pays_id')->constrained('pays')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
```

**Exécuter la migration :**

```bash
php artisan migrate
```

---

### Étape 2 : Créer le modèle Eloquent

```bash
php artisan make:model Department
```

**Fichier : `app/Models/Department.php`**

```php
<?php

namespace App\Models;

use App\Enums\StatutDepartment;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * Table associée au modèle
     */
    protected $table = 'departments';

    /**
     * Les attributs qui sont mass assignable
     */
    protected $fillable = [
        'nom',
        'code',
        'description',
        'statut',
        'pays_id',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'statut' => StatutDepartment::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */

    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class);
    }

    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class);
    }

    /**
     * Scopes
     */

    public function scopeActif($query)
    {
        return $query->where('statut', StatutDepartment::ACTIF);
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', StatutDepartment::INACTIF);
    }
}
```

---

### Étape 3 : Créer l'Enum

**Fichier : `app/Enums/StatutDepartment.php`**

```php
<?php

namespace App\Enums;

enum StatutDepartment: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';

    /**
     * Obtenir tous les statuts
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Obtenir le label du statut
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
        };
    }

    /**
     * Vérifier si le statut est actif
     */
    public function isActif(): bool
    {
        return $this === self::ACTIF;
    }
}
```

---

### Étape 4 : Créer l'interface du Repository

**Fichier : `app/Repositories/Contracts/DepartmentRepositoryInterface.php`**

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Récupérer tous les départements actifs
     */
    public function getActifs(): Collection;

    /**
     * Rechercher un département par son code
     */
    public function findByCode(string $code): ?Department;

    /**
     * Récupérer les départements d'un pays
     */
    public function getByPays(string $paysId): Collection;

    /**
     * Vérifier si un code existe déjà
     */
    public function codeExists(string $code, ?string $excludeId = null): bool;
}
```

---

### Étape 5 : Créer l'implémentation du Repository

**Fichier : `app/Repositories/DepartmentRepository.php`**

```php
<?php

namespace App\Repositories;

use App\Enums\StatutDepartment;
use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    /**
     * Récupérer tous les départements actifs
     */
    public function getActifs(): Collection
    {
        return $this->model
            ->where('statut', StatutDepartment::ACTIF)
            ->with('pays')
            ->orderBy('nom')
            ->get();
    }

    /**
     * Rechercher un département par son code
     */
    public function findByCode(string $code): ?Department
    {
        return $this->model
            ->where('code', $code)
            ->with('pays')
            ->first();
    }

    /**
     * Récupérer les départements d'un pays
     */
    public function getByPays(string $paysId): Collection
    {
        return $this->model
            ->where('pays_id', $paysId)
            ->where('statut', StatutDepartment::ACTIF)
            ->orderBy('nom')
            ->get();
    }

    /**
     * Vérifier si un code existe déjà
     */
    public function codeExists(string $code, ?string $excludeId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
```

---

### Étape 6 : Créer l'interface du Service

**Fichier : `app/Services/Contracts/DepartmentServiceInterface.php`**

```php
<?php

namespace App\Services\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentServiceInterface
{
    /**
     * Récupérer tous les départements
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Récupérer un département par son ID
     */
    public function find(string $id): ?Department;

    /**
     * Créer un nouveau département
     */
    public function create(array $data): Department;

    /**
     * Mettre à jour un département
     */
    public function update(string $id, array $data): Department;

    /**
     * Supprimer un département (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Activer un département
     */
    public function activer(string $id): Department;

    /**
     * Désactiver un département
     */
    public function desactiver(string $id): Department;
}
```

---

### Étape 7 : Créer l'implémentation du Service

**Fichier : `app/Services/DepartmentService.php`**

```php
<?php

namespace App\Services;

use App\Enums\StatutDepartment;
use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentService implements DepartmentServiceInterface
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository
    ) {}

    /**
     * Récupérer tous les départements
     */
    public function getAll(array $filters = []): Collection
    {
        if (isset($filters['statut']) && $filters['statut'] === 'actif') {
            return $this->departmentRepository->getActifs();
        }

        if (isset($filters['pays_id'])) {
            return $this->departmentRepository->getByPays($filters['pays_id']);
        }

        return $this->departmentRepository->all();
    }

    /**
     * Récupérer un département par son ID
     */
    public function find(string $id): ?Department
    {
        return $this->departmentRepository->find($id);
    }

    /**
     * Créer un nouveau département
     */
    public function create(array $data): Department
    {
        // Validation métier : vérifier si le code existe déjà
        if ($this->departmentRepository->codeExists($data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Ce code de département existe déjà.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Créer le département
            $department = $this->departmentRepository->create($data);

            DB::commit();

            return $department->load('pays');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour un département
     */
    public function update(string $id, array $data): Department
    {
        // Validation métier : vérifier si le code existe déjà (sauf pour ce département)
        if (isset($data['code']) && $this->departmentRepository->codeExists($data['code'], $id)) {
            throw ValidationException::withMessages([
                'code' => 'Ce code de département existe déjà.'
            ]);
        }

        DB::beginTransaction();

        try {
            $department = $this->departmentRepository->update($id, $data);

            DB::commit();

            return $department->load('pays');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer un département
     */
    public function delete(string $id): bool
    {
        // Validation métier : vérifier si le département a des villes
        $department = $this->find($id);

        if ($department->villes()->exists()) {
            throw ValidationException::withMessages([
                'department' => 'Ce département ne peut pas être supprimé car il contient des villes.'
            ]);
        }

        return $this->departmentRepository->delete($id);
    }

    /**
     * Activer un département
     */
    public function activer(string $id): Department
    {
        return $this->departmentRepository->update($id, [
            'statut' => StatutDepartment::ACTIF
        ]);
    }

    /**
     * Désactiver un département
     */
    public function desactiver(string $id): Department
    {
        return $this->departmentRepository->update($id, [
            'statut' => StatutDepartment::INACTIF
        ]);
    }
}
```

---

### Étape 8 : Enregistrer dans le ServiceProvider

**Fichier : `app/Providers/ServiceLayerServiceProvider.php`**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// ... autres imports ...

// Ajouter les nouveaux imports
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\DepartmentRepository;
use App\Services\Contracts\DepartmentServiceInterface;
use App\Services\DepartmentService;

class ServiceLayerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ... enregistrements existants ...

        // ✅ AJOUTER : Enregistrer le repository
        $this->app->bind(
            DepartmentRepositoryInterface::class,
            DepartmentRepository::class
        );

        // ✅ AJOUTER : Enregistrer le service
        $this->app->bind(
            DepartmentServiceInterface::class,
            DepartmentService::class
        );
    }
}
```

---

### Étape 9 : Créer les Form Requests

#### Request de création

**Fichier : `app/Http/Requests/CreateDepartmentRequest.php`**

```php
<?php

namespace App\Http\Requests;

use App\Enums\StatutDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDepartmentRequest extends FormRequest
{
    /**
     * Autoriser la requête
     */
    public function authorize(): bool
    {
        return true; // ou vérifier les permissions
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'statut' => ['nullable', Rule::enum(StatutDepartment::class)],
            'pays_id' => ['required', 'exists:pays,id'],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du département est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'code.required' => 'Le code du département est obligatoire.',
            'code.unique' => 'Ce code de département existe déjà.',
            'pays_id.required' => 'Le pays est obligatoire.',
            'pays_id.exists' => 'Le pays sélectionné n\'existe pas.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages
     */
    public function attributes(): array
    {
        return [
            'nom' => 'nom du département',
            'code' => 'code',
            'pays_id' => 'pays',
        ];
    }
}
```

#### Request de mise à jour

**Fichier : `app/Http/Requests/UpdateDepartmentRequest.php`**

```php
<?php

namespace App\Http\Requests;

use App\Enums\StatutDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('id');

        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'string',
                'max:10',
                Rule::unique('departments', 'code')->ignore($departmentId)
            ],
            'description' => ['nullable', 'string'],
            'statut' => ['sometimes', Rule::enum(StatutDepartment::class)],
            'pays_id' => ['sometimes', 'exists:pays,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Ce code de département existe déjà.',
            'pays_id.exists' => 'Le pays sélectionné n\'existe pas.',
        ];
    }
}
```

---

### Étape 10 : Créer l'API Resource

**Fichier : `app/Http/Resources/DepartmentResource.php`**

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transformer en tableau
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'description' => $this->description,
            'statut' => $this->statut->value,
            'statut_label' => $this->statut->label(),

            // Relations (eager loaded)
            'pays' => new PaysResource($this->whenLoaded('pays')),
            'villes' => VilleResource::collection($this->whenLoaded('villes')),

            // Métadonnées
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

---

### Étape 11 : Créer le Controller

**Fichier : `app/Http/Controllers/Api/DepartmentController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\Contracts\DepartmentServiceInterface;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use JsonResponseTrait;

    public function __construct(
        private DepartmentServiceInterface $departmentService
    ) {}

    /**
     * Lister tous les départements
     *
     * @group Départements
     */
    public function index(Request $request): JsonResponse
    {
        $departments = $this->departmentService->getAll($request->query());

        return $this->successResponse(
            DepartmentResource::collection($departments),
            'Liste des départements récupérée avec succès'
        );
    }

    /**
     * Afficher un département
     *
     * @group Départements
     */
    public function show(string $id): JsonResponse
    {
        $department = $this->departmentService->find($id);

        if (!$department) {
            return $this->notFoundResponse('Département non trouvé');
        }

        return $this->successResponse(
            new DepartmentResource($department),
            'Département récupéré avec succès'
        );
    }

    /**
     * Créer un département
     *
     * @group Départements
     */
    public function store(CreateDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->create($request->validated());

        return $this->createdResponse(
            new DepartmentResource($department),
            'Département créé avec succès'
        );
    }

    /**
     * Mettre à jour un département
     *
     * @group Départements
     */
    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        $department = $this->departmentService->update($id, $request->validated());

        return $this->successResponse(
            new DepartmentResource($department),
            'Département mis à jour avec succès'
        );
    }

    /**
     * Supprimer un département
     *
     * @group Départements
     */
    public function destroy(string $id): JsonResponse
    {
        $this->departmentService->delete($id);

        return $this->successResponse(
            null,
            'Département supprimé avec succès'
        );
    }

    /**
     * Activer un département
     *
     * @group Départements
     */
    public function activer(string $id): JsonResponse
    {
        $department = $this->departmentService->activer($id);

        return $this->successResponse(
            new DepartmentResource($department),
            'Département activé avec succès'
        );
    }

    /**
     * Désactiver un département
     *
     * @group Départements
     */
    public function desactiver(string $id): JsonResponse
    {
        $department = $this->departmentService->desactiver($id);

        return $this->successResponse(
            new DepartmentResource($department),
            'Département désactivé avec succès'
        );
    }
}
```

---

### Étape 12 : Définir les routes

**Fichier : `routes/api.php`**

```php
use App\Http\Controllers\Api\DepartmentController;

// Routes publiques (si nécessaire)
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{id}', [DepartmentController::class, 'show']);

// Routes protégées par authentification
Route::middleware(['auth:api'])->group(function () {

    // CRUD départements
    Route::post('/departments', [DepartmentController::class, 'store'])
        ->middleware('can:create-department');

    Route::put('/departments/{id}', [DepartmentController::class, 'update'])
        ->middleware('can:update-department');

    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])
        ->middleware('can:delete-department');

    // Actions spécifiques
    Route::post('/departments/{id}/activer', [DepartmentController::class, 'activer'])
        ->middleware('can:manage-department');

    Route::post('/departments/{id}/desactiver', [DepartmentController::class, 'desactiver'])
        ->middleware('can:manage-department');
});
```

---

### Étape 13 : Ajouter les permissions (RBAC)

**Créer un Seeder pour les permissions :**

```bash
php artisan make:seeder DepartmentPermissionsSeeder
```

**Fichier : `database/seeders/DepartmentPermissionsSeeder.php`**

```php
<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class DepartmentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'view-department',
                'description' => 'Voir les départements',
                'module' => 'departments'
            ],
            [
                'name' => 'create-department',
                'description' => 'Créer un département',
                'module' => 'departments'
            ],
            [
                'name' => 'update-department',
                'description' => 'Modifier un département',
                'module' => 'departments'
            ],
            [
                'name' => 'delete-department',
                'description' => 'Supprimer un département',
                'module' => 'departments'
            ],
            [
                'name' => 'manage-department',
                'description' => 'Gérer les départements (activer/désactiver)',
                'module' => 'departments'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
```

**Exécuter le seeder :**

```bash
php artisan db:seed --class=DepartmentPermissionsSeeder
```

---

### Étape 14 : Tester l'API

#### Avec cURL

```bash
# 1. Créer un département
curl -X POST http://localhost:8000/api/departments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "nom": "Paris",
    "code": "75",
    "description": "Département de Paris",
    "pays_id": "01HQ..."
  }'

# 2. Lister les départements
curl http://localhost:8000/api/departments

# 3. Récupérer un département
curl http://localhost:8000/api/departments/01HQ...

# 4. Mettre à jour
curl -X PUT http://localhost:8000/api/departments/01HQ... \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"nom": "Paris Intra-Muros"}'

# 5. Supprimer
curl -X DELETE http://localhost:8000/api/departments/01HQ... \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Avec Postman

1. Importer la collection Swagger
2. Tester chaque endpoint
3. Vérifier les validations
4. Tester les cas d'erreur

---

### Étape 15 : Documenter avec Swagger

**Ajouter les annotations Swagger dans le Controller :**

```php
/**
 * @OA\Get(
 *     path="/api/departments",
 *     summary="Lister tous les départements",
 *     tags={"Départements"},
 *     @OA\Parameter(
 *         name="statut",
 *         in="query",
 *         description="Filtrer par statut (actif/inactif)",
 *         required=false,
 *         @OA\Schema(type="string", enum={"actif", "inactif"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des départements",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Department"))
 *         )
 *     )
 * )
 */
public function index(Request $request): JsonResponse
{
    // ...
}
```

**Générer la documentation :**

```bash
php artisan l5-swagger:generate
```

**Accéder à la documentation :**

```
http://localhost:8000/api/documentation
```

---

## Composants détaillés

### JsonResponseTrait

Ce trait fournit des méthodes pour standardiser les réponses JSON :

```php
namespace App\Traits;

trait JsonResponseTrait
{
    /**
     * Réponse de succès (200)
     */
    protected function successResponse($data, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Réponse de création (201)
     */
    protected function createdResponse($data, string $message = 'Created')
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse d'erreur (4xx, 5xx)
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Ressource non trouvée (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Non autorisé (403)
     */
    protected function forbiddenResponse(string $message = 'Forbidden')
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Erreur de validation (422)
     */
    protected function validationErrorResponse($errors)
    {
        return $this->errorResponse('Validation error', 422, $errors);
    }
}
```

---

## Gestion des erreurs

### Validation automatique avec FormRequest

Laravel gère automatiquement les erreurs de validation et retourne une réponse 422 :

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nom": ["Le nom du département est obligatoire."],
    "code": ["Ce code de département existe déjà."]
  }
}
```

### Exceptions personnalisées

**Créer une exception métier :**

```php
namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(
        string $message,
        private array $errors = []
    ) {
        parent::__construct($message);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], 400);
    }
}
```

**Utilisation dans le Service :**

```php
if ($this->departmentRepository->codeExists($data['code'])) {
    throw new BusinessException('Validation error', [
        'code' => 'Ce code de département existe déjà.'
    ]);
}
```

---

## Tests

### Test unitaire du Repository

```php
namespace Tests\Unit\Repositories;

use App\Models\Department;
use App\Repositories\DepartmentRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DepartmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DepartmentRepository(new Department());
    }

    public function test_can_create_department()
    {
        $data = [
            'nom' => 'Paris',
            'code' => '75',
            'pays_id' => Pays::factory()->create()->id,
        ];

        $department = $this->repository->create($data);

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Paris', $department->nom);
    }

    public function test_can_find_by_code()
    {
        Department::factory()->create(['code' => '75']);

        $department = $this->repository->findByCode('75');

        $this->assertNotNull($department);
        $this->assertEquals('75', $department->code);
    }
}
```

### Test d'intégration de l'API

```php
namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function test_can_list_departments()
    {
        Department::factory()->count(3)->create();

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'nom', 'code']
                ]
            ]);
    }

    public function test_can_create_department()
    {
        $data = [
            'nom' => 'Paris',
            'code' => '75',
            'pays_id' => Pays::factory()->create()->id,
        ];

        $response = $this->postJson('/api/departments', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => 'Paris',
                    'code' => '75',
                ]
            ]);

        $this->assertDatabaseHas('departments', ['code' => '75']);
    }

    public function test_cannot_create_department_with_duplicate_code()
    {
        Department::factory()->create(['code' => '75']);

        $data = [
            'nom' => 'Paris',
            'code' => '75',
            'pays_id' => Pays::factory()->create()->id,
        ];

        $response = $this->postJson('/api/departments', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
```

---

## Résumé

Pour créer une nouvelle API REST complète :

1. ✅ **Migration** → Structure BDD
2. ✅ **Model** → Eloquent + Relations
3. ✅ **Enum** → Constantes typées
4. ✅ **Repository** → Interface + Implémentation (accès données)
5. ✅ **Service** → Interface + Implémentation (logique métier)
6. ✅ **ServiceProvider** → Enregistrer les bindings
7. ✅ **FormRequest** → Validation
8. ✅ **Resource** → Formatage JSON
9. ✅ **Controller** → Gestion HTTP
10. ✅ **Routes** → Endpoints + Middleware
11. ✅ **Permissions** → RBAC
12. ✅ **Tests** → Unitaires + Intégration
13. ✅ **Documentation** → Swagger

---

## Prochaines étapes

📖 Consultez aussi :
- [Architecture SOLID](ARCHITECTURE.md)
- [Exemples concrets](EXAMPLES.md)
- [Bonnes pratiques](BEST_PRACTICES.md)
