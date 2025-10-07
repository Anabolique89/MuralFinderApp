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

        // Update basic fields
        $post->update([
            'status' => $request->status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        // If publishing, use the model's publish method to set published_at
        if ($request->status === 'published') {
            $post->publish();
        }

        return $this->sendSuccess($post->fresh(), 'Post status updated successfully');
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
        // Get settings from database using the Setting model
        $settings = [
            'site_name' => \App\Models\Setting::get('site_name', config('app.name', 'MuralFinder')),
            'site_description' => \App\Models\Setting::get('site_description', 'Discover and share street art around the world'),
            'site_url' => \App\Models\Setting::get('site_url', config('app.url')),
            'admin_email' => \App\Models\Setting::get('admin_email', config('mail.from.address')),
            'max_upload_size' => \App\Models\Setting::get('max_upload_size', 10),
            'allowed_file_types' => \App\Models\Setting::get('allowed_file_types', 'jpg,jpeg,png,gif,mp4,mov'),
            'auto_approve_artworks' => \App\Models\Setting::get('auto_approve_artworks', false),
            'auto_approve_posts' => \App\Models\Setting::get('auto_approve_posts', false),
            'require_email_verification' => \App\Models\Setting::get('require_email_verification', true),
            'enable_notifications' => \App\Models\Setting::get('enable_notifications', true),
            'maintenance_mode' => \App\Models\Setting::get('maintenance_mode', false),
            'max_artworks_per_user' => \App\Models\Setting::get('max_artworks_per_user', 50),
            'max_posts_per_user' => \App\Models\Setting::get('max_posts_per_user', 20),
            'enable_comments' => \App\Models\Setting::get('enable_comments', true),
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
            'max_upload_size' => 'nullable|integer|min:1|max:100',
            'allowed_file_types' => 'nullable|string',
            'auto_approve_artworks' => 'nullable|boolean',
            'auto_approve_posts' => 'nullable|boolean',
            'require_email_verification' => 'nullable|boolean',
            'enable_notifications' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'max_artworks_per_user' => 'nullable|integer|min:1|max:1000',
            'max_posts_per_user' => 'nullable|integer|min:1|max:1000',
            'enable_comments' => 'nullable|boolean',
        ]);

        try {
            // Save settings using the Setting model
            $settingsToSave = $request->only([
                'site_name',
                'site_description',
                'site_url',
                'admin_email',
                'max_upload_size',
                'allowed_file_types',
                'auto_approve_artworks',
                'auto_approve_posts',
                'require_email_verification',
                'enable_notifications',
                'maintenance_mode',
                'max_artworks_per_user',
                'max_posts_per_user',
                'enable_comments'
            ]);

            foreach ($settingsToSave as $key => $value) {
                if ($value !== null) {
                    \App\Models\Setting::set($key, $value, is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'));
                }
            }

            return $this->sendSuccess(null, 'Settings updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update settings: ' . $e->getMessage(), 500);
        }
    }
}
