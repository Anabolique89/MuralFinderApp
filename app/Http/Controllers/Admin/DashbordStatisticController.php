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
        $recentArtworks = Artwork::with('user')->get()->take(10);
        $users = User::with('profile')->paginate(10);

        $data = compact('artworkCount', 'postCount', 'userCount', 'wallsCount', 'recentArtworks', 'users');

        return $this->sendSuccess($data, 'Statistics retrieved successfully');
    }

    public function getArtworksStatistics()
    {
        $artworks = Artwork::with('user')->get();
        $data = compact('artworks');
        return $this->sendSuccess($data, 'Artworks statistics retrieved successfully');
    }
}
