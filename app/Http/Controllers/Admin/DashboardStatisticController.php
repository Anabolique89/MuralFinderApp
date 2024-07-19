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
}