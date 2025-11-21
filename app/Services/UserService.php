<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Получить список пользователей с пагинацией и поиском
     *
     * @param int $page
     * @param string $search
     * @return LengthAwarePaginator
     */
    public function getUsers(int $page, string $search): LengthAwarePaginator
    {
        return User::orderBy('id', 'desc')
            ->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->paginate(12, ['*'], 'page', $page);
    }

    /**
     * Создать нового пользователя
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    /**
     * Обновить данные пользователя
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }
        
        return $user;
    }

    /**
     * Удалить пользователя
     *
     * @param User $user
     * @return bool|null
     */
    public function deleteUser(User $user): ?bool
    {
        return $user->delete();
    }

    /**
     * Проверить права доступа для просмотра списка пользователей
     *
     * @return bool
     */
    public function canViewAny(): bool
    {
        return Gate::allows('viewAny', User::class);
    }

    /**
     * Проверить права доступа для создания пользователя
     *
     * @return bool
     */
    public function canCreate(): bool
    {
        return Gate::allows('create', User::class);
    }

    /**
     * Проверить права доступа для просмотра пользователя
     *
     * @param User $user
     * @return bool
     */
    public function canView(User $user): bool
    {
        return Gate::allows('view', $user);
    }

    /**
     * Проверить права доступа для обновления пользователя
     *
     * @param User $user
     * @return bool
     */
    public function canUpdate(User $user): bool
    {
        return Gate::allows('update', $user);
    }

    /**
     * Проверить права доступа для удаления пользователя
     *
     * @param User $user
     * @return bool
     */
    public function canDelete(User $user): bool
    {
        return Gate::allows('delete', $user);
    }
}