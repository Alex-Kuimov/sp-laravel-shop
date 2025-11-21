<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page   = $request['page'] ?? 1;
        $search = $request['search'] ?? '';

        // Только админ может просматривать всех пользователей
        if (! Gate::allows('viewAny', User::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return User::orderBy('id', 'desc')
            ->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->paginate(12, ['*'], 'page', $page);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        if (! Gate::allows('create', User::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Пользователь может просматривать только себя, админ может просматривать любого
        $user = User::findOrFail($id);

        if (! Gate::allows('view', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $user = User::findOrFail($id);

        if (! Gate::allows('update', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update($request->only(['name', 'email']));

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (! Gate::allows('delete', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

}
