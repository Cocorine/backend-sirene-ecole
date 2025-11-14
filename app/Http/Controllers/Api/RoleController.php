<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Role\AssignPermissionsRequest;
use App\Http\Requests\API\Role\CreateRoleRequest;
use App\Http\Requests\API\Role\RemovePermissionsRequest;
use App\Http\Requests\API\Role\SyncPermissionsRequest;
use App\Http\Requests\API\Role\UpdateRoleRequest;
use App\Services\Contracts\RoleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

/**
 * Class RoleController
 * @package App\Http\Controllers\API
 * @OA\Tag(
 *     name="Roles",
 *     description="API Endpoints of Roles"
 * )
 * @OA\Schema(
 *     schema="Role",
 *     title="Role",
 *     description="Role model",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the permission"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the permission"
 *     )
 * )
 * Controller for managing Roles.
 */
class RoleController extends Controller implements HasMiddleware
{
    protected RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
        /*$this->middleware('can:voir_les_roles')->only('index');
        $this->middleware('can:creer_role')->only('store');
        $this->middleware('can:voir_role')->only('show');
        $this->middleware('can:modifier_role')->only('update');
        $this->middleware('can:supprimer_role')->only('destroy');
        $this->middleware('can:assigner_permissions_role')->only(['assignPermissions', 'syncPermissions', 'removePermissions']);
        */
    }

    public static function middleware(): array
    {
        return [
            /*new Middleware('can:voir_les_roles', only: ['index']),
            new Middleware('can:creer_role', only: ['store']),
            new Middleware('can:voir_role', only: ['show']),
            new Middleware('can:modifier_role', only: ['update']),
            new Middleware('can:supprimer_role', only: ['destroy']),
            new Middleware('can:assigner_permissions_role', only: ['assignPermissions', 'syncPermissions', 'removePermissions']),*/
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="Display a listing of roles",
     *     operationId="getRoles",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of roles per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Role"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        Gate::authorize('voir_les_roles');
        return $this->roleService->getAll(15, ['permissions']);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="Store a newly created role in storage",
     *     operationId="storeRole",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Role")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        Gate::authorize('creer_role');
        return $this->roleService->createRole($request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Display the specified role",
     *     operationId="getRoleById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the role to retrieve",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Role")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('voir_role');
        return $this->roleService->getById($id, relations:['permissions']);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Update the specified role in storage",
     *     operationId="updateRole",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the role to update",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Role")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function update(UpdateRoleRequest $request, string $id): JsonResponse
    {
        Gate::authorize('modifier_role');
        return $this->roleService->updateRole($id, $request->validated());
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Remove the specified role from storage",
     *     operationId="deleteRole",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the role to delete",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Role deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('supprimer_role');
        return $this->roleService->delete($id);
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{roleId}/permissions/assign",
     *     tags={"Roles"},
     *     summary="Assign permissions to a role",
     *     operationId="assignPermissionsToRole",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID of the role to assign permissions to",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AssignPermissionsRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions assigned successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"permission_ids": {"The permission ids field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function assignPermissions(AssignPermissionsRequest $request, string $roleId): JsonResponse
    {
        Gate::authorize('assigner_permissions_role');
        return $this->roleService->assignPermissionsToRole($roleId, $request->input('permission_ids'));
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{roleId}/permissions/sync",
     *     tags={"Roles"},
     *     summary="Sync permissions for a role",
     *     operationId="syncPermissionsForRole",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID of the role to sync permissions for",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SyncPermissionsRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions synced successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"permission_ids": {"The permission ids field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function syncPermissions(SyncPermissionsRequest $request, string $roleId): JsonResponse
    {
        Gate::authorize('assigner_permissions_role');
        return $this->roleService->syncPermissionsToRole($roleId, $request->input('permission_ids'));
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{roleId}/permissions/remove",
     *     tags={"Roles"},
     *     summary="Remove permissions from a role",
     *     operationId="removePermissionsFromRole",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID of the role to remove permissions from",
     *         required=true,
     *         @OA\Schema(type="string", format="ulid", example="01ARZ3NDEKTSV4WS06X8Q1J8Q1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RemovePermissionsRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permissions removed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"permission_ids": {"The permission ids field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function removePermissions(RemovePermissionsRequest $request, string $roleId): JsonResponse
    {
        Gate::authorize('assigner_permissions_role');
        return $this->roleService->removePermissionsFromRole($roleId, $request->input('permission_ids'));
    }
}
