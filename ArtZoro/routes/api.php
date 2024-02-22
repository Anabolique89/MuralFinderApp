<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginApiController;
use App\Http\Controllers\Auth\LogoutApiController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterApiController;
use App\Http\Controllers\Auth\ResendEmailVerificationController;
use App\Http\Controllers\FellowshipController;
use App\Http\Controllers\ProfileApiController;
use App\Http\Controllers\ArtworkController;
use Illuminate\Http\Request;
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

Route::post('/forgot-password', [PasswordResetController::class, 'sendPasswordResetToken'])->name('password.email');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
/*
 * Authenticated routes
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', LogoutApiController::class);

    Route::prefix('profiles')->group(function () {
        Route::post('/', [ProfileApiController::class, 'create']);
        Route::get('{id}', [ProfileApiController::class, 'show']);
        Route::put('{id}', [ProfileApiController::class, 'update']);
        Route::delete('{id}', [ProfileApiController::class, 'destroy']);

        Route::post('{id}/image', [ProfileApiController::class, 'uploadProfileImage']);
    });

    Route::prefix('fellowships')->group(function () {
        Route::post('follow', [FellowshipController::class, 'followUser']);
        Route::post('unfollow', [FellowshipController::class, 'unfollowUser']);
        Route::get('followers', [FellowshipController::class, 'getUserFollowers']);
        Route::get('followings', [FellowshipController::class, 'getUserFollowings']);
    });

    Route::prefix('artworks')->group(function () {
        Route::post('', [ArtworkController::class, 'store']);
        Route::put('/{artwork}', [ArtworkController::class, 'update']);
        Route::delete('/{artwork}', [ArtworkController::class, 'destroy']);
        Route::post('/{artwork}/image', [ArtworkController::class, 'changeImage']);
        Route::delete('/{artwork}/unlike', [ArtworkController::class, 'unlike']);
        Route::post('/{artwork}/like', [ArtworkController::class, 'like']);
        Route::post('/{artwork}/comment', [ArtworkController::class, 'comment']);
    });
});


Route::prefix('artworks')->group(function () {
    Route::get('', [ArtworkController::class, 'index']);
    Route::get('{artwork}', [ArtworkController::class, 'show']);


});