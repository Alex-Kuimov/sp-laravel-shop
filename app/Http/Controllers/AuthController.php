<?php
namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\AuthResetRequest;
use App\Http\Requests\AuthSendResetLinkRequest;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    /**
     * Register a new user.
     */
    public function register(AuthRegisterRequest $request)
    {
        $user = $this->authService->register($request->only(['name', 'email', 'password']));

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Login user.
     */
    public function login(AuthLoginRequest $request)
    {
        $result = $this->authService->login($request->email, $request->password);

        if (! $result['success']) {
            return ApiResponse::validationError($result['errors'], $result['message']);
        }

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
        ]);
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(AuthSendResetLinkRequest $request)
    {
        $result = $this->authService->sendResetLinkEmail($request->email);

        return $result['success']
            ? ApiResponse::success(null, $result['message'])
            : ApiResponse::error($result['message']);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(AuthResetRequest $request)
    {
        $result = $this->authService->reset($request->only('email', 'password', 'password_confirmation', 'token'));

        return $result['success']
            ? ApiResponse::success(null, $result['message'])
            : ApiResponse::error($result['message']);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Logged out successfully');
    }
}
