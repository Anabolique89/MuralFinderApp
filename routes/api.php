<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginApiController;
use App\Http\Controllers\Auth\LogoutApiController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterApiController;
use App\Http\Controllers\Auth\ResendEmailVerificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FellowshipController;
use App\Http\Controllers\ProfileApiController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\Admin\DashboardStatisticController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductApiController;
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

Route::post('register', RegisterApiController::class);
Route::get('/verify-email/{id}/{hash}', EmailVerificationController::class)->name('verification.verify');
Route::post('/email/verification/resend', ResendEmailVerificationController::class)->name('email.send');
Route::post('login', LoginApiController::class);
Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);

Route::post('/forgot-password', [PasswordResetController::class, 'sendPasswordResetToken'])->name('password.email');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');


/*
 * Authenticated routes
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', LogoutApiController::class);

    Route::put('change-password', PasswordChangeController::class)->name('profile.password.update');

    Route::delete('delete/user/{id}', [ProfileApiController::class, 'deleteUser']);
    Route::prefix('profiles')->group(function () {
        Route::post('/', [ProfileApiController::class, 'create']);
        Route::get('{id}', [ProfileApiController::class, 'show']);
        Route::put('{id}', [ProfileApiController::class, 'update']);
        Route::delete('{id}', [ProfileApiController::class, 'destroy']);
        Route::put('update/{id}/role', [ProfileApiController::class, 'updateRole'])->name('profile.role.update');

        Route::post('{id}/image', [ProfileApiController::class, 'uploadProfileImage']);
    });

    Route::prefix('fellowships')->group(function () {
        Route::post('follow', [FellowshipController::class, 'followUser']);
        Route::post('unfollow', [FellowshipController::class, 'unfollowUser']);
        Route::get('followers', [FellowshipController::class, 'getUserFollowers']);
        Route::get('followings', [FellowshipController::class, 'getUserFollowings']);
        Route::get('isFollowing/{userId}', [FellowshipController::class, 'isFollowingUser']);
    });

    Route::prefix('artworks')->group(function () {
        Route::post('', [ArtworkController::class, 'store']);
        Route::post('/{artwork}', [ArtworkController::class, 'update']);
        Route::delete('/{artwork}', [ArtworkController::class, 'destroy']);
        Route::post('/{artwork}/image', [ArtworkController::class, 'changeImage']);
        Route::delete('/{artwork}/unlike', [ArtworkController::class, 'unlike']);
        Route::post('/{artwork}/like', [ArtworkController::class, 'like']);
        Route::post('/{artwork}/comment', [ArtworkController::class, 'comment']);
        Route::get('/{artwork}/comments', [ArtworkController::class, 'getComments']);
        Route::delete('/comments/{comment}', [ArtworkController::class, 'deleteComment']);
        Route::put('/comments/{comment}/edit', [ArtworkController::class, 'editComment']);
    });

    Route::prefix('posts')->group(function () {
        Route::post('', [CommunityPostController::class, 'store']);
        Route::post('/{post}', [CommunityPostController::class, 'update']);
        Route::delete('/{post}', [CommunityPostController::class, 'destroy']);
        Route::post('/{post}/image', [CommunityPostController::class, 'changeImage']);
        Route::delete('/{post}/unlike', [CommunityPostController::class, 'unlike']);
        Route::post('/{post}/like', [CommunityPostController::class, 'like']);
        Route::post('/{posts}/comment', [CommunityPostController::class, 'comment']);
        Route::put('comments/{comment}/edit', [CommunityPostController::class, 'editComment']);
        Route::delete('/comments/{comment}', [CommunityPostController::class, 'deleteComment']);
    });

    Route::group(['prefix' => 'walls'], function () {
        Route::post('/', [WallController::class, 'store']);
        Route::post('/{id}', [WallController::class, 'update']);
        Route::delete('/{id}', [WallController::class, 'destroy']);
        Route::put('/{id}/verify', [WallController::class, 'verifyWall']);
        Route::post('/{id}/like', [WallController::class, 'toggleLike']);
        Route::post('/{id}/comments', [WallController::class, 'addComment']);
        Route::delete('/{wallId}/comments/{commentId}', [WallController::class, 'deleteComment']);
        Route::put('/{wallId}/comments/{commentId}', [WallController::class, 'updateComment']);
    });


    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);

    Route::apiResource('products', ProductApiController::class);

});


Route::prefix('artworks')->group(function () {
    Route::get('', [ArtworkController::class, 'index'])->name('artworks.index');
    Route::get('/artwork/withliked', [ArtworkController::class, 'indexWithLiked'])->name('artworks.grouped');
    Route::get('/artwork/ungrouped', [ArtworkController::class, 'getAllUngrouped'])->name('artworks.ungrouped');
    Route::get('{artwork}', [ArtworkController::class, 'show'])->name('artworks.show');
    Route::get('artwork/search', [ArtworkController::class, 'search'])->name('artworks.search'); // Use 'find' or another descriptive prefix
    Route::get('/categories/fetch', [ArtworkController::class, 'getCategories'])->name('artwork.categories');
});

Route::prefix('posts')->group(function () {
    Route::get('', [CommunityPostController::class, 'index'])->name('posts.index');
    Route::get('post/{userId}/get', [CommunityPostController::class, 'postsByUser']);
    Route::get('{post}', [CommunityPostController::class, 'show'])->name('posts.show');
    Route::get('{post}/comments', [CommunityPostController::class, 'getPostComments'])->name('posts.loadcomments');
    Route::get('post/search', [CommunityPostController::class, 'search'])->name('posts.search'); // Use 'find' or another descriptive prefix
});

Route::group(['prefix' => 'walls'], function () {
    Route::get('/', [WallController::class, 'index']);
    Route::get('/{id}', [WallController::class, 'show']);
    Route::get('/{id}/comments', [WallController::class, 'getComments']);
    Route::get('/search', [WallController::class, 'search']);
});


Route::group(['prefix' => 'admin/statistics'], function () {
    Route::get('', [DashboardStatisticController::class, 'getStatistics']);
    Route::get('/artworks', [DashboardStatisticController::class, 'getArtworksStatistics']);
    Route::get('/posts', [DashboardStatisticController::class, 'getPostsStatistics']);
    Route::get('/walls', [DashboardStatisticController::class, 'getWallsStatistics']);
    Route::get('/users', [DashboardStatisticController::class, 'getUserStatistics']);
});

Route::group(['prefix' => 'admin/trash'], function () {
    Route::get('', [TrashController::class, 'getAll']);
    Route::post('/{model}/{id}/restore', [TrashController::class, 'restore']);
    Route::delete('/{model}/{id}', [TrashController::class, 'delete']);
});

Route::post('/contact', [ContactController::class, 'contactUs']);
Route::get('users/search', [ProfileApiController::class, 'search']);
Route::get('artworks/users/{userId}', [ArtworkController::class, 'getUserArtworks']);
Route::get('profiles/{id}', [ProfileApiController::class, 'show']);
Route::get('artworks/{artwork}/comments', [ArtworkController::class, 'getComments']);


Route::get('/test-notification/{userId}/{entityType}/{entityId}', [NotificationController::class, 'testNotification']);

Route::post('broadcasting/auth', function (Illuminate\Http\Request $request) {
    Log::debug('Broadcast auth request:', $request->all());

    try {
        return Broadcast::auth($request);
    } catch (\Exception $e) {
        Log::error('Broadcast auth failed:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Broadcast authorization failed.'], 403);
    }
})->middleware('auth:sanctum');
