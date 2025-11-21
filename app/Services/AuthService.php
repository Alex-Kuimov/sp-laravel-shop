<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Регистрация нового пользователя
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    /**
     * Аутентификация пользователя
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();
        
        if (! $user) {
            return [
                'success' => false,
                'message' => 'The selected email is invalid.',
                'errors' => [
                    'email' => ['The selected email is invalid.']
                ]
            ];
        }

        if (! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'password' => ['The password is incorrect.']
                ]
            ];
        }

        Auth::login($user);
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Отправка ссылки для сброса пароля
     *
     * @param string $email
     * @return array
     */
    public function sendResetLinkEmail(string $email): array
    {
        $status = Password::sendResetLink(['email' => $email]);

        return [
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => __($status)
        ];
    }

    /**
     * Сброс пароля пользователя
     *
     * @param array $data
     * @return array
     */
    public function reset(array $data): array
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return [
            'success' => $status === Password::PASSWORD_RESET,
            'message' => __($status)
        ];
    }

    /**
     * Выход пользователя из системы
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }
}