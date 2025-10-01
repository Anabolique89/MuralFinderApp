<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticationService
{
    protected UserRepository $userRepository;
    protected UserService $userService;

    public function __construct(UserRepository $userRepository, UserService $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Register a new user
     */
    public function register(array $userData): array
    {
        return DB::transaction(function () use ($userData) {
            // Create user with profile
            $user = $this->userService->createUser([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'role' => $userData['role'] ?? User::ROLE_ARTLOVER,
            ], [
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
            ]);

            // Send email verification
            $user->sendEmailVerificationNotification();

            // Fire registered event
            event(new Registered($user));

            // Log activity
            $this->logActivity($user, 'registered', null, [
                'registration_method' => 'email',
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
            ]);

            return [
                'user' => $this->formatUserResponse($user),
                'message' => 'Registration successful. Please verify your email address.',
            ];
        });
    }

    /**
     * Authenticate user and return tokens
     */
    public function login(string $identifier, string $password, bool $remember = false): array
    {
        // Determine if identifier is email or username
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find user
        $user = $field === 'email'
            ? $this->userRepository->findByEmail($identifier)
            : $this->userRepository->findByUsername($identifier);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'account' => ['Your account is not active. Please contact support.'],
            ]);
        }

        // Check email verification
        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email address before logging in.'],
            ]);
        }

        // Create tokens
        $tokens = $this->createTokens($user, $remember);

        // Update last login
        $this->userService->updateLastLogin($user);

        // Fire login event
        event(new Login('sanctum', $user, $remember));

        // Log activity
        $this->logActivity($user, 'login', null, [
            'login_method' => 'email',
            'remember' => $remember,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        return [
            'user' => $this->formatUserResponse($user),
            'tokens' => $tokens,
            'message' => 'Login successful',
        ];
    }

    /**
     * Logout user and revoke tokens
     */
    public function logout(User $user, string $currentToken = null): array
    {
        if ($currentToken) {
            // Revoke only current token
            $token = PersonalAccessToken::findToken($currentToken);
            if ($token && $token->tokenable_id === $user->id) {
                $token->delete();
            }
        } else {
            // Revoke all tokens
            $user->tokens()->delete();
        }

        // Fire logout event
        event(new Logout('sanctum', $user));

        // Log activity
        $this->logActivity($user, 'logout', null, [
            'logout_type' => $currentToken ? 'single_device' : 'all_devices',
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        return [
            'message' => 'Logout successful',
        ];
    }

    /**
     * Refresh access token
     */
    public function refreshToken(User $user, string $refreshToken): array
    {
        // Find refresh token
        $token = $user->tokens()
            ->where('name', 'refresh_token')
            ->where('token', hash('sha256', $refreshToken))
            ->first();

        if (!$token || $token->expires_at < now()) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired refresh token.'],
            ]);
        }

        // Create new tokens
        $tokens = $this->createTokens($user);

        // Delete old refresh token
        $token->delete();

        // Log activity
        $this->logActivity($user, 'token_refreshed');

        return [
            'tokens' => $tokens,
            'message' => 'Token refreshed successfully',
        ];
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            // Don't reveal if email exists for security
            return [
                'message' => 'If an account with that email exists, we have sent a password reset link.',
            ];
        }

        // Send password reset notification
        $user->sendPasswordResetNotification(
            app('auth.password.broker')->createToken($user)
        );

        // Log activity
        $this->logActivity($user, 'password_reset_requested');

        return [
            'message' => 'Password reset link sent to your email address.',
        ];
    }

    /**
     * Reset password
     */
    public function resetPassword(string $email, string $token, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email address.'],
            ]);
        }

        // Verify token
        if (!app('auth.password.broker')->tokenExists($user, $token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired password reset token.'],
            ]);
        }

        // Update password
        $this->userService->changePassword($user, $password);

        // Delete password reset token
        app('auth.password.broker')->deleteToken($user);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        // Log activity
        $this->logActivity($user, 'password_reset_completed');

        return [
            'message' => 'Password reset successful. Please login with your new password.',
        ];
    }

    /**
     * Verify email address
     */
    public function verifyEmail(User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified.',
            ];
        }

        $user->markEmailAsVerified();

        // Log activity
        $this->logActivity($user, 'email_verified');

        return [
            'message' => 'Email verified successfully.',
        ];
    }

    /**
     * Create access and refresh tokens
     */
    protected function createTokens(User $user, bool $remember = false): array
    {
        // Create access token (expires in 24 hours)
        $accessToken = $user->createToken('access_token', ['*'], now()->addDay());

        // Create refresh token (expires in 30 days or 1 year if remember me)
        $refreshTokenExpiry = $remember ? now()->addYear() : now()->addDays(30);
        $refreshToken = $user->createToken('refresh_token', ['refresh'], $refreshTokenExpiry);

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 86400, // 24 hours in seconds
        ];
    }

    /**
     * Format user response
     */
    protected function formatUserResponse(User $user): array
    {
        $user->load('profile');

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'profile' => $user->profile ? [
                'first_name' => $user->profile->first_name,
                'last_name' => $user->profile->last_name,
                'bio' => $user->profile->bio,
                'profile_image_url' => $user->profile->profile_image_url,
                'location' => $user->profile->location,
                'followers_count' => $user->profile->followers_count,
                'following_count' => $user->profile->following_count,
                'artworks_count' => $user->profile->artworks_count,
                'posts_count' => $user->profile->posts_count,
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Log user activity
     */
    protected function logActivity(User $user, string $action, $subject = null, array $metadata = []): void
    {
        UserActivityLog::logAction($user, $action, $subject, $metadata);
    }

    /**
     * Get user from token
     */
    public function getUserFromToken(string $token): ?User
    {
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || $accessToken->expires_at < now()) {
            return null;
        }

        return $accessToken->tokenable;
    }

    /**
     * Revoke all user tokens except current
     */
    public function revokeOtherTokens(User $user, string $currentToken): array
    {
        $current = PersonalAccessToken::findToken($currentToken);

        $user->tokens()
            ->where('id', '!=', $current?->id)
            ->delete();

        // Log activity
        $this->logActivity($user, 'other_sessions_revoked');

        return [
            'message' => 'All other sessions have been logged out.',
        ];
    }
}
