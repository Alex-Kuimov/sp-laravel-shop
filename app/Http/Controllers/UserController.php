<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page   = $request['page'] ?? 1;
        $search = $request['search'] ?? '';

        // Только админ может просматривать всех пользователей
        if (! $this->userService->canViewAny()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = $this->userService->getUsers($page, $search);

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        if (! $this->userService->canCreate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = $this->userService->createUser($request->only(['name', 'email', 'password', 'role']));

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Пользователь может просматривать только себя, админ может просматривать любого
        $user = User::findOrFail($id);

        if (! $this->userService->canView($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $user = User::findOrFail($id);

        if (! $this->userService->canUpdate($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = $this->userService->updateUser($user, $request->only(['name', 'email', 'password']));

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (! $this->userService->canDelete($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->userService->deleteUser($user);

        return response()->json(['message' => 'User deleted successfully']);
    }

}
