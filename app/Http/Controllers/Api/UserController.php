<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return $this->userService->getAll(15, relations: ["userInfo"]);
    }

    public function show(string $id)
    {
        return $this->userService->getById($id, relations:["userInfo"]);
    }

    public function store(StoreUserRequest $request)
    {
        return $this->userService->create($request->validated());
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        return $this->userService->update($id, $request->validated());
    }

    public function destroy(string $id)
    {
        return $this->userService->delete($id);
    }
}
