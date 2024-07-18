<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\ApiBaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artwork;
use App\Models\Post;
use App\Models\User;
use App\Models\Wall;

class DashbordStatisticController extends ApiBaseController
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
    $page = $request->input('page', 1);
    $perPage = 10; // Set the number of artworks per page

    $artworks = Artwork::with('user', 'category', 'likes', 'comments')
        ->withCount(['likes', 'comments'])
        ->paginate($perPage, ['*'], 'page', $page);

    $data = [
        'artworks' => $artworks->items(),
        'total' => $artworks->total(),
        'currentPage' => $artworks->currentPage(),
        'lastPage' => $artworks->lastPage(),
        'perPage' => $artworks->perPage(),
    ];

    return $this->sendSuccess($data, 'Artworks statistics retrieved successfully');
}
}
