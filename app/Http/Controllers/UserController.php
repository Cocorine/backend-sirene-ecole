<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return $this->userService->getAll();
    }

    public function show(string $id)
    {
        return $this->userService->getById($id);
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
