<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\ApiBaseController;
use Illuminate\Http\Request;
use App\Models\Artwork;
use App\Models\Post;
use App\Models\Product;
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
        $productsCount = Product::count();
        $recentArtworks = Artwork::with('user', 'category')->get()->take(10);
        $users = User::with('profile')->paginate(10);

        $data = compact('artworkCount', 'postCount', 'userCount', 'wallsCount', 'recentArtworks', 'users');

        return $this->sendSuccess($data, 'Statistics retrieved successfully');
    }

    public function getArtworksStatistics(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = 10;

        // Paginate the artworks
        $artworks = Artwork::with('user', 'category', 'likes', 'comments')
            ->withCount(['likes', 'comments'])
            ->paginate($perPage);

        $wallsCount = Wall::count();
        $deletedArtworks = Artwork::onlyTrashed()->count();

        $data = [
            'wallsCount' => $wallsCount,
            'artworks' => $artworks->items(),
            'deletedArtworks' => $deletedArtworks,
            'artworksCount' => $artworks->total(),
            'likesCount' => $artworks->sum('likes_count'),
            'commentsCount' => $artworks->sum('comments_count'),
            'currentPage' => $artworks->currentPage(),
            'lastPage' => $artworks->lastPage(),
            'perPage' => $artworks->perPage(),
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
    public function getUserStatistics(Request $request)
    {
        // Set the number of users per page
        $perPage = 10;

        // Paginate the users
        $users = User::with('profile')
            ->withCount(['posts', 'artworks', 'followers', 'followings'])
            ->paginate($perPage);

        // Map user statistics
        $userStats = $users->map(function($user) {
            return [
                'id' => $user->id,
                'profile' => $user->profile,
                'username' => $user->username,
                'role' => $user->role,
                'email' => $user->email,
                'postsCount' => $user->posts_count,
                'artworksCount' => $user->artworks_count,
                'followersCount' => $user->followers_count,
                'followingsCount' => $user->followings_count,
            ];
        });

        // Prepare the data with pagination details
        $data = [
            'totalUsers' => $users->total(),
            'currentPage' => $users->currentPage(),
            'lastPage' => $users->lastPage(),
            'perPage' => $users->perPage(),
            'userStatistics' => $userStats->toArray()
        ];

        return $this->sendSuccess($data, 'User statistics retrieved successfully');
    }


    public function getProductsStatistics()
{
    $productsCount = Product::count();
    $deletedProducts = Product::onlyTrashed()->count();

    $data = [
        'productsCount' => $productsCount,
        'deletedProducts' => $deletedProducts,
    ];

    return $this->sendSuccess($data, 'Product statistics retrieved successfully');
}

public function getProducts(Request $request)
{
    $page = $request->input('page', 1);
    $perPage = 10;

    // Paginate products without additional relationships
    $products = Product::paginate($perPage, ['*'], 'page', $page);

    return $this->sendSuccess($products, 'Products retrieved successfully');
}




}
