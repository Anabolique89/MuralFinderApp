<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginApiController;
use App\Http\Controllers\Auth\LogoutApiController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterApiController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SocialAuthController;

// API Controllers
use App\Http\Controllers\Api\ArtworkApiController;
use App\Http\Controllers\Api\WallApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SearchApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\AIGeneratorController;

// Non-API Controllers
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\DashboardStatisticController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*
 * Unauthenticated routes
 */

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterApiController::class);
    Route::post('/login', LoginApiController::class);

    // Password reset
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);

    // Email verification
    Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->name('verification.verify');

    // Social authentication
    Route::get('/{provider}', [SocialAuthController::class, 'redirectToProvider']);
    Route::get('/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
});


/*
 * Authenticated routes
 */
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', LogoutApiController::class);
    Route::post('/auth/refresh-token', RefreshTokenController::class);
    Route::post('/auth/email/verify', [EmailVerificationController::class, 'verify']);
    Route::post('/auth/email/resend', [EmailVerificationController::class, 'resend']);

    // User Management
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserApiController::class, 'profile']);
        Route::put('/profile', [UserApiController::class, 'updateProfile']);
        Route::post('/profile/image', [UserApiController::class, 'uploadProfileImage']);
        Route::put('/change-password', [UserApiController::class, 'changePassword']);
        Route::post('/deactivate', [UserApiController::class, 'deactivateAccount']);
        Route::get('/activity-feed', [UserApiController::class, 'activityFeed']);
    });

    // User Social Features (Authenticated)
    Route::prefix('users')->group(function () {
        Route::post('/{username}/follow', [UserApiController::class, 'toggleFollow']);
    });
// muralfinder.net
// server ip
// nginx

// api.muralfinder.net
// apiv2.muralfinder.net
    // Artworks
    Route::prefix('artworks')->group(function () {
        Route::post('/', [ArtworkApiController::class, 'store']); // CREATE   // POST api.muralfinder.net/artworks/
        Route::put('/{id}', [ArtworkApiController::class, 'update']); // EDIT / UPDATE
        Route::delete('/{id}', [ArtworkApiController::class, 'destroy']); // DELETE
        Route::post('/{id}/like', [ArtworkApiController::class, 'toggleLike']);
        Route::get('/{id}/comments', [ArtworkApiController::class, 'comments']);
        Route::post('/{id}/comments', [ArtworkApiController::class, 'addComment']);
    });

    // Walls
    Route::prefix('walls')->group(function () {
        Route::post('/', [WallApiController::class, 'store']);
        Route::put('/{id}', [WallApiController::class, 'update']);
        Route::delete('/{id}', [WallApiController::class, 'destroy']);
        Route::post('/{id}/like', [WallApiController::class, 'toggleLike']);
        Route::post('/{id}/comments', [WallApiController::class, 'addComment']);
        Route::post('/{id}/checkin', [WallApiController::class, 'checkIn']);
    });

    // Posts
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostApiController::class, 'store']);
        Route::put('/{id}', [PostApiController::class, 'update']);
        Route::delete('/{id}', [PostApiController::class, 'destroy']);
        Route::post('/{id}/like', [PostApiController::class, 'toggleLike']);
        Route::get('/{id}/comments', [PostApiController::class, 'comments']);
        Route::post('/{id}/comments', [PostApiController::class, 'addComment']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationApiController::class, 'index']);
        Route::get('/unread-count', [NotificationApiController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationApiController::class, 'destroy']);
        Route::get('/preferences', [NotificationApiController::class, 'preferences']);
        Route::put('/preferences', [NotificationApiController::class, 'updatePreferences']);
    });

    // Device Tokens for Push Notifications
    Route::prefix('device-tokens')->group(function () {
        Route::get('/', [DeviceTokenController::class, 'index']);
        Route::post('/', [DeviceTokenController::class, 'register']);
        Route::put('/{id}', [DeviceTokenController::class, 'update']);
        Route::post('/{id}/deactivate', [DeviceTokenController::class, 'deactivate']);
        Route::delete('/{id}', [DeviceTokenController::class, 'destroy']);
    });

    // AI Generator
    Route::prefix('ai-generator')->group(function () {
        Route::post('/generate-archetype', [AIGeneratorController::class, 'generateArchetype']);
        Route::post('/forge-saga', [AIGeneratorController::class, 'forgeSaga']);
        Route::post('/generate-custom', [AIGeneratorController::class, 'generateCustom']);
        Route::post('/forge-custom-saga', [AIGeneratorController::class, 'forgeCustomSaga']);
        Route::post('/upload-as-artwork', [AIGeneratorController::class, 'uploadAsArtwork']);
    });

    // Legacy API endpoints (to be deprecated)
    Route::get('/contact', [ContactController::class, 'contactUs']);

});

// Public API Routes
Route::prefix('v1')->group(function () {
    // Artworks (Public)
    Route::prefix('artworks')->group(function () {
        Route::get('/', [ArtworkApiController::class, 'index']);
        Route::get('/featured', [ArtworkApiController::class, 'featured']);
        Route::get('/nearby', [ArtworkApiController::class, 'nearby']);
        Route::get('/search', [ArtworkApiController::class, 'search']);
        Route::get('/{id}', [ArtworkApiController::class, 'show']);
    });

    // Walls (Public)
    Route::prefix('walls')->group(function () {
        Route::get('/', [WallApiController::class, 'index']);
        Route::get('/nearby', [WallApiController::class, 'nearby']);
        Route::get('/search', [WallApiController::class, 'search']);
        Route::get('/{id}', [WallApiController::class, 'show']);
        Route::get('/{id}/comments', [WallApiController::class, 'comments']);
    });

    // Posts (Public)
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostApiController::class, 'index']);
        Route::get('/featured', [PostApiController::class, 'featured']);
        Route::get('/trending', [PostApiController::class, 'trending']);
        Route::get('/search', [PostApiController::class, 'search']);
        Route::get('/{id}', [PostApiController::class, 'show']);
    });

    // Users (Public)
    Route::prefix('users')->group(function () {
        Route::get('/search', [UserApiController::class, 'search']);
        Route::get('/{username}', [UserApiController::class, 'show']);
        Route::get('/{username}/artworks', [UserApiController::class, 'artworks']);
        Route::get('/{username}/walls', [UserApiController::class, 'walls']);
        Route::get('/{username}/posts', [UserApiController::class, 'posts']);
        Route::get('/{username}/followers', [UserApiController::class, 'followers']);
        Route::get('/{username}/following', [UserApiController::class, 'following']);
    });

    // Search (Public)
    Route::prefix('search')->group(function () {
        Route::get('/global', [SearchApiController::class, 'global']);
        Route::get('/artworks', [SearchApiController::class, 'artworks']);
        Route::get('/posts', [SearchApiController::class, 'posts']);
        Route::get('/walls', [SearchApiController::class, 'walls']);
        Route::get('/users', [SearchApiController::class, 'users']);
        Route::get('/suggestions', [SearchApiController::class, 'suggestions']);
        Route::get('/trending', [SearchApiController::class, 'trending']);
        Route::post('/click', [SearchApiController::class, 'recordClick']);
        Route::post('/advanced', [SearchApiController::class, 'advanced']);
    });

    // AI Generator (Public)
    Route::prefix('ai-generator')->group(function () {
        Route::get('/archetypes', [AIGeneratorController::class, 'getArchetypes']);
    });
});

// Legacy routes (for backward compatibility) - DEPRECATED
// These routes are maintained for backward compatibility but should use new API controllers
Route::prefix('legacy')->group(function () {
    // Categories endpoint (still needed)
    Route::get('/categories', function () {
        return app(\App\Repositories\CategoryRepository::class)->getActive();
    });
});

Route::prefix('reports')->group(function () {
    Route::get('/', [ReportsController::class, 'index']);
    Route::post('/', [ReportsController::class, 'store']);
    Route::get('/{id}', [ReportsController::class, 'show']);
    Route::put('/{id}', [ReportsController::class, 'update']);
    Route::delete('/{id}', [ReportsController::class, 'destroy']);
    Route::get('/filter/user/{userId}', [ReportsController::class, 'filterByUser']);
    Route::get('/filter/type/{type}', [ReportsController::class, 'filterByType']);
    Route::get('/search', [ReportsController::class, 'search']);
});

// Walls legacy routes removed - use /api/v1/walls instead


Route::group(['prefix' => 'admin/statistics'], function () {
    Route::get('', [DashboardStatisticController::class, 'getStatistics']);
    Route::get('/artworks', [DashboardStatisticController::class, 'getArtworksStatistics']);
    Route::get('/posts', [DashboardStatisticController::class, 'getPostsStatistics']);
    Route::get('/walls', [DashboardStatisticController::class, 'getWallsStatistics']);
    Route::get('/users', [DashboardStatisticController::class, 'getUserStatistics']);
    Route::get('/products', [DashboardStatisticController::class, 'getProductsStatistics']);
});

Route::group(['prefix' => 'admin/trash'], function () {
    Route::get('', [TrashController::class, 'getAll']);
    Route::post('/{model}/{id}/restore', [TrashController::class, 'restore']);
    Route::delete('/{model}/{id}', [TrashController::class, 'delete']);
});

// Admin CRUD routes (protected by auth middleware)
Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'admin'], function () {
        // User management
        Route::put('/users/{userId}/role', [AdminController::class, 'updateUserRole']);
        Route::post('/users/{userId}/ban', [AdminController::class, 'banUser']);
        Route::post('/users/{userId}/unban', [AdminController::class, 'unbanUser']);
        Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);

        // Artwork management
        Route::put('/artworks/{artworkId}/status', [AdminController::class, 'updateArtworkStatus']);
        Route::delete('/artworks/{artworkId}', [AdminController::class, 'deleteArtwork']);

        // Wall management
        Route::put('/walls/{wallId}/status', [AdminController::class, 'updateWallStatus']);
        Route::delete('/walls/{wallId}', [AdminController::class, 'deleteWall']);

        // Post management
        Route::put('/posts/{postId}/status', [AdminController::class, 'updatePostStatus']);
        Route::delete('/posts/{postId}', [AdminController::class, 'deletePost']);

        // Settings management
        Route::get('/settings', [AdminController::class, 'getSettings']);
        Route::put('/settings', [AdminController::class, 'updateSettings']);
    });
});

// Contact endpoint
Route::post('/contact', [ContactController::class, 'contactUs']);

Route::post('broadcasting/auth', function (Illuminate\Http\Request $request) {
    Log::debug('Broadcast auth request:', $request->all());

    try {
        return Broadcast::auth($request);
    } catch (\Exception $e) {
        Log::error('Broadcast auth failed:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Broadcast authorization failed.'], 403);
    }
})->middleware('auth:sanctum');
