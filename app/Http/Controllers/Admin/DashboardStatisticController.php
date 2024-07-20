<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\ApiBaseController;
use Illuminate\Http\Request;
use App\Models\Artwork;
use App\Models\Post;
use App\Models\User;
use App\Models\Wall;

class DashboardStatisticController extends ApiBaseController
{
    public function getStatistics()
    {
        $artworkCount = Artwork::count();
        $postCount = Post::count();
        $userCount = User::count();
        $wallsCount = Wall::count();
        $recentArtworks = Artwork::with('user', 'category')->get()->take(10);
        $users = User::with('profile')->paginate(10);

        $data = compact('artworkCount', 'postCount', 'userCount', 'wallsCount', 'recentArtworks', 'users');

        return $this->sendSuccess($data, 'Statistics retrieved successfully');
    }

    public function getArtworksStatistics(Request $request)
    {
        $artworks = Artwork::with('user', 'category', 'likes', 'comments')
            ->withCount(['likes', 'comments'])
            ->get();
        $wallsCount = Wall::count();
        $deletedArtworks = Artwork::onlyTrashed()->count();

        $data = [
            'wallsCount' => $wallsCount,
            'artworks' => $artworks,
            'deletedArtworks' => $deletedArtworks,
            'artworksCount' => $artworks->count(),
            'likesCount' => $artworks->sum('likes_count'),
            'commentsCount' => $artworks->sum('comments_count')
        ];

        return $this->sendSuccess($data, 'Artworks statistics retrieved successfully');
    }

    public function getPosts(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 10; // Set the number of posts per page

        $posts = Post::with('user', 'likes', 'comments')
            ->withCount(['likes', 'comments'])
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->sendSuccess($posts, 'Posts retrieved successfully');
    }

    public function getPostsStatistics()
    {
        $posts = Post::with('likes', 'comments')
            ->withCount(['likes', 'comments'])
            ->get();
        $deletedPosts = Post::onlyTrashed()->count();

        $data = [
            'postsCount' => $posts->count(),
            'likesCount' => $posts->sum('likes_count'),
            'commentsCount' => $posts->sum('comments_count'),
            'deletedPosts' => $deletedPosts,
        ];

        return $this->sendSuccess($data, 'Posts statistics retrieved successfully');
    }

    public function getWallsStatistics()
    {
        $verified = Wall::where('is_verified', true)->count();
        $unverified = Wall::where('is_verified', false)->count();
        $wallsCount = Wall::count();
        $deletedCount = Wall::onlyTrashed()->count();

        $data = [
            'verified' => $verified,
            'unverified' => $unverified,
            'wallsCount' => $wallsCount,
            'deletedCount' => $deletedCount,
        ];
        return $this->sendSuccess($data, 'Wall statistics retrieved successfully');
    }

    private function getWalls(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 10;

        $walls = Wall::with('addedBy')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->sendSuccess($walls, 'Walls retrieved successfully');
    }
    public function getUserStatistics()
    {
        $users = User::withCount(['posts', 'artworks', 'followers', 'followings'])->get();
        $totalUsers = $users->count();

        $userStats = $users->map(function($user) {
            return [
                'username' => $user->username,
                'email' => $user->email,
                'postsCount' => $user->posts_count,
                'artworksCount' => $user->artworks_count,
                'followersCount' => $user->followers_count,
                'followingsCount' => $user->followings_count,
            ];
        });

        $data = [
            'totalUsers' => $totalUsers,
            'userStatistics' => $userStats
        ];

        return $this->sendSuccess($data, 'User statistics retrieved successfully');
    }



}
