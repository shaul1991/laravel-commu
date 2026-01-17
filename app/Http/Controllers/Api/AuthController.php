<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUserInput;
use App\Application\Auth\LoginUserUseCase;
use App\Application\Auth\RegisterUserInput;
use App\Application\Auth\RegisterUserUseCase;
use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\Exceptions\InvalidEmailException;
use App\Domain\Core\User\Exceptions\InvalidUsernameException;
use App\Domain\Core\User\Exceptions\WeakPasswordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUseCase,
        private readonly LoginUserUseCase $loginUseCase
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->registerUseCase->execute(
                new RegisterUserInput(
                    name: $request->validated('name'),
                    email: $request->validated('email'),
                    username: $request->validated('username'),
                    password: $request->validated('password')
                )
            );

            // Get the UserModel to create a token for auto-login
            $userModel = UserModel::where('uuid', $user->id()->value())->firstOrFail();
            $token = $userModel->createToken('auth-token')->plainTextToken;

            // Establish web session for SPA
            Auth::login($userModel);

            return response()->json([
                'data' => [
                    'user' => $this->transformUser($user),
                    'token' => $token,
                ],
                'message' => 'User registered successfully',
            ], 201);
        } catch (InvalidEmailException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['email' => [$e->getMessage()]],
            ], 422);
        } catch (InvalidUsernameException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['username' => [$e->getMessage()]],
            ], 422);
        } catch (WeakPasswordException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['password' => [$e->getMessage()]],
            ], 422);
        } catch (\DomainException $e) {
            $field = str_contains($e->getMessage(), 'Email') ? 'email' : 'username';

            return response()->json([
                'message' => 'Validation failed',
                'errors' => [$field => [$e->getMessage()]],
            ], 422);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->loginUseCase->execute(
                new LoginUserInput(
                    emailOrUsername: $request->validated('email'),
                    password: $request->validated('password')
                )
            );

            // Establish web session for SPA
            $userModel = UserModel::where('uuid', $result->user->id()->value())->firstOrFail();
            Auth::login($userModel, $request->boolean('remember'));

            return response()->json([
                'data' => [
                    'user' => $this->transformUser($result->user),
                    'token' => $result->token,
                ],
                'message' => 'Login successful',
            ]);
        } catch (InvalidCredentialsException) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete();

        // Clear web session if available
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'data' => [
                'id' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = UserModel::where('email', $request->validated('email'))->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        // Always return success to prevent email enumeration
        return response()->json([
            'message' => 'Password reset link sent to your email',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully',
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired token',
        ], 400);
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent',
        ]);
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        /** @var UserModel $user */
        $user = $request->user();

        if ($user->getKey() !== $id) {
            return response()->json([
                'message' => 'Invalid verification link',
            ], 403);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id()->value(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'username' => $user->username()->value(),
            'bio' => $user->bio(),
            'avatar_url' => $user->avatarUrl(),
            'email_verified_at' => $user->emailVerifiedAt()?->format('c'),
            'created_at' => $user->createdAt()->format('c'),
        ];
    }
}
