<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Artwork;
use App\Models\ArtworkImage;
use App\Models\ArtworkLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ArtworkController extends ApiBaseController
{
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $userId = Auth::id();

        $artworks = Artwork::with(['category', 'user.profile', 'likes' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
        ->withCount('likes', 'comments')
        ->paginate($pageSize);

        $groupedArtworks = $artworks->groupBy(fn ($artwork) => $artwork->category?->name ?? 'others');
        $groupedArtworksWithPagination = [];

        foreach ($groupedArtworks as $category => $items) {
            $itemsWithLikedProperty = $items->map(fn ($artwork) => [
                'id' => $artwork->id,
                'title' => $artwork->title,
                'category' => $artwork->category?->name ?? 'others',
                'user' => $artwork->user->profile,
                'likes_count' => $artwork->likes_count,
                'comments_count' => $artwork->comments_count,
                'liked' => $artwork->likes->contains('user_id', $userId),
            ]);

            $groupedArtworksWithPagination[] = [
                'category' => $category,
                'artworks' => $itemsWithLikedProperty,
                'total' => $artworks->total(),
                'current_page' => $artworks->currentPage(),
                'last_page' => $artworks->lastPage(),
            ];
        }

        return $this->sendSuccess($groupedArtworksWithPagination, 'Artworks grouped by category retrieved successfully');
    }

    public function show($artworkId)
    {
        $artwork = Artwork::with(['user.profile', 'category'])
            ->withCount(['likes', 'comments'])
            ->findOrFail($artworkId);

        $artwork->liked = $this->isLiked($artwork);

        return $this->sendSuccess($artwork, 'Artwork retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'artwork_category_id' => 'required|exists:artwork_categories,id',
            'images.*' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        $artworkData = $validator->validated();
        $artworkData['user_id'] = Auth::id();
        $artworkData['image_path'] = null;

        $artwork = Artwork::create($artworkData);
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $this->uploadImage($image, 'artworks', 'public');
                if (!$imagePath) {
                    return $this->sendError('Failed to upload image');
                }
                $imagePaths[] = $imagePath;
            }

            $artwork->update(['image_path' => $imagePaths[0]]);
        }

        foreach ($imagePaths as $imagePath) {
            $artworkImage = new ArtworkImage(['artwork_url' => $imagePath]);
            $artwork->images()->save($artworkImage);
        }

        $artwork->load('images');

        return $this->sendSuccess($artwork, 'Artwork created successfully');
    }

    public function update(Request $request, Artwork $artwork)
    {
        if ($artwork->user_id !== Auth::id()) {
            return $this->sendError("Cannot update another person's artwork");
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($artwork->image_path);
            $data['image_path'] = $this->uploadImage($request->file('image'), 'artworks', 'public');
        }

        $artwork->update($data);

        return $this->sendSuccess($artwork, 'Artwork updated successfully');
    }

    public function destroy($id)
    {
        $artwork = Artwork::find($id);
        if (!$artwork) {
            return $this->sendError('Artwork not found');
        }

        if ($artwork->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return $this->sendError("Cannot delete another person's artwork");
        }

        try {
            $artwork->delete();
            return $this->sendSuccess(null, 'Artwork deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error Deleting Artwork: ' . $e->getMessage());
            return $this->sendError('Internal server error');
        }
    }

    public function like(Artwork $artwork)
    {
        $userId = Auth::id();

        if (ArtworkLike::where('user_id', $userId)->where('artwork_id', $artwork->id)->exists()) {
            return $this->sendError('You have already liked this artwork');
        }

        try {
            $like = ArtworkLike::create([
                'user_id' => $userId,
                'artwork_id' => $artwork->id,
            ]);

            return $this->sendSuccess($like, 'Artwork liked successfully');
        } catch (\Exception $e) {
            Log::error('Error liking artwork: ' . $e->getMessage());
            return $this->sendError('An error occurred while liking artwork');
        }
    }

    public function unlike(Artwork $artwork)
    {
        $userId = Auth::id();
        $like = ArtworkLike::where('user_id', $userId)->where('artwork_id', $artwork->id)->first();

        if (!$like) {
            return $this->sendError('You have not liked this artwork');
        }

        try {
            $like->delete();
            return $this->sendSuccess(null, 'Artwork unliked successfully');
        } catch (\Exception $e) {
            Log::error('Error unliking artwork: ' . $e->getMessage());
            return $this->sendError('An error occurred while unliking artwork');
        }
    }

    private function isLiked($artwork)
    {
        return ArtworkLike::where('artwork_id', $artwork->id)
            ->where('user_id', Auth::id())
            ->exists();
    }
}
