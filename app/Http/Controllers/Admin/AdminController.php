<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\User;
use App\Models\Artwork;
use App\Models\Wall;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends ApiBaseController
{
    // User Management
    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'artist', 'artlover', 'moderator'])]
        ]);

        $user = User::findOrFail($userId);
        $user->update(['role' => $request->role]);

        return $this->sendSuccess($user, 'User role updated successfully');
    }

    public function banUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'banned']);

        return $this->sendSuccess($user, 'User banned successfully');
    }

    public function unbanUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'active']);

        return $this->sendSuccess($user, 'User unbanned successfully');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        // Soft delete the user
        $user->delete();

        return $this->sendSuccess(null, 'User deleted successfully');
    }

    // Artwork Management
    public function updateArtworkStatus(Request $request, $artworkId)
    {
        $request->validate([
            'status' => ['required', Rule::in(['published', 'draft', 'pending', 'rejected'])],
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $artwork = Artwork::findOrFail($artworkId);
        $artwork->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return $this->sendSuccess($artwork, 'Artwork status updated successfully');
    }

    public function deleteArtwork($artworkId)
    {
        $artwork = Artwork::findOrFail($artworkId);

        // Soft delete the artwork
        $artwork->delete();

        return $this->sendSuccess(null, 'Artwork deleted successfully');
    }

    // Wall Management
    public function updateWallStatus(Request $request, $wallId)
    {
        $request->validate([
            'status' => ['required', Rule::in(['verified', 'pending', 'rejected'])],
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $wall = Wall::findOrFail($wallId);
        $wall->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => auth()->id(),
            'verified_at' => $request->status === 'verified' ? now() : null
        ]);

        return $this->sendSuccess($wall, 'Wall status updated successfully');
    }

    public function deleteWall($wallId)
    {
        $wall = Wall::findOrFail($wallId);

        // Soft delete the wall
        $wall->delete();

        return $this->sendSuccess(null, 'Wall deleted successfully');
    }

    // Post Management
    public function updatePostStatus(Request $request, $postId)
    {
        $request->validate([
            'status' => ['required', Rule::in(['published', 'draft', 'pending', 'archived'])]
        ]);

        $post = Post::findOrFail($postId);
        $post->update([
            'status' => $request->status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return $this->sendSuccess($post, 'Post status updated successfully');
    }

    public function deletePost($postId)
    {
        $post = Post::findOrFail($postId);

        // Soft delete the post
        $post->delete();

        return $this->sendSuccess(null, 'Post deleted successfully');
    }

    // Settings Management
    public function getSettings()
    {
        // For now, return default settings since we don't have a settings table
        $settings = [
            'site_name' => config('app.name', 'MuralFinder'),
            'site_description' => 'Discover and share street art around the world',
            'site_url' => config('app.url'),
            'admin_email' => config('mail.from.address'),
            'user_registration' => true,
            'email_verification' => true,
            'comment_moderation' => false,
            'artwork_approval' => true,
            'wall_approval' => false,
            'email_notifications' => true,
            'push_notifications' => false,
            'admin_notifications' => true,
            'user_notifications' => true,
            'max_file_size' => 5,
            'allowed_file_types' => 'jpg,jpeg,png,gif,webp',
            'posts_per_page' => 12,
            'comments_per_page' => 20,
            'session_timeout' => 60,
            'password_min_length' => 8,
            'require_2fa' => false,
            'login_attempts' => 5,
        ];

        return $this->sendSuccess($settings, 'Settings retrieved successfully');
    }

    public function updateSettings(Request $request)
    {
        // Validate the settings
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_url' => 'nullable|url',
            'admin_email' => 'nullable|email',
            'user_registration' => 'boolean',
            'email_verification' => 'boolean',
            'comment_moderation' => 'boolean',
            'artwork_approval' => 'boolean',
            'wall_approval' => 'boolean',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'admin_notifications' => 'boolean',
            'user_notifications' => 'boolean',
            'max_file_size' => 'integer|min:1|max:100',
            'allowed_file_types' => 'string',
            'posts_per_page' => 'integer|min:1|max:100',
            'comments_per_page' => 'integer|min:1|max:100',
            'session_timeout' => 'integer|min:5|max:1440',
            'password_min_length' => 'integer|min:6|max:50',
            'require_2fa' => 'boolean',
            'login_attempts' => 'integer|min:1|max:20',
        ]);

        // For now, just return success since we don't have a settings table
        // In a real app, you would save these to a database or config files
        

        return $this->sendSuccess($request->all(), 'Settings updated successfully');
    }
}
