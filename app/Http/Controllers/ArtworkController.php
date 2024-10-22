<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkComment;
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

        $userId = $request->input('user_id') ?? Auth::id();

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
                'image_path' => $artwork->image_path,
                'category' => $artwork->category?->name ?? 'others',
                'user' => $artwork->user->profile,
                'likes_count' => $artwork->likes_count,
                'comments_count' => $artwork->comments_count,
                'liked' => $this->isLiked($artwork->id, $userId),
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

    public function show(Request $request, $artworkId)
    {
        $userId = $request->input('user_id') ?? Auth::id();


        $artwork = Artwork::with(['user.profile', 'category'])
            ->withCount(['likes', 'comments'])
            ->findOrFail($artworkId);

        $artwork->liked = $this->isLiked($artwork->id, $userId);

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

    public function search(Request $request)
    {
        try {
            $userId = $request->input('user_id') ?? Auth::id();
            $searchQuery = $request->get('query');

            $query = Artwork::with(['category', 'user.profile', 'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);

            if ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('title', 'like', '%' . $searchQuery . '%')
                        ->orWhere('description', 'like', '%' . $searchQuery . '%')
                        ->orWhereHas('user', function ($query) use ($searchQuery) {
                            $query->where('username', 'like', '%' . $searchQuery . '%');
                        })
                        ->orWhereHas('category', function ($query) use ($searchQuery) {
                            $query->where('name', 'like', '%' . $searchQuery . '%');
                        });
                });
            }

            $pageSize = $request->get('pageSize', 15);
            $artworks = $query->paginate($pageSize);

            $artworksWithLikedProperty = $artworks->getCollection()->map(function ($artwork) use ($userId) {
                $artwork->liked = $this->isLiked($artwork->id, $userId);
                return $artwork;
            });

            $artworks->setCollection($artworksWithLikedProperty);

            return $this->sendSuccess($artworks, "Artworks searched successfully");
        } catch (\Exception $e) {
            Log::error('Error in search function: ' . $e->getMessage());
            return $this->sendError('An error occurred while searching for artworks');
        }
    }

    public function getUserArtworks(Request $request, $userId)
    {
        $pageSize = $request->query('pageSize', 10);

        $artworks = Artwork::with('category', 'user.profile')
            ->withCount('likes', 'comments')
            ->where('user_id', $userId)
            ->paginate($pageSize);

        $groupedArtworks = $artworks->groupBy(fn ($artwork) => $artwork->category ? $artwork->category->name : 'others');
        $userArtworks = [];

        foreach ($groupedArtworks as $category => $items) {
            $itemsWithLikedProperty = $items->map(function ($artwork) use ($userId) {
                $artwork->liked = $this->isLiked($artwork->id, $userId);
                return $artwork;
            });

            $userArtworks[] = [
                'category' => $category,
                'artworks' => $itemsWithLikedProperty,
            ];
        }

        return $this->sendSuccess($userArtworks, 'User artworks retrieved successfully');
    }


    public function getAllUngrouped(Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $userId = $request->input('user_id') ?? Auth::id();

        $artworks = Artwork::with('user.profile')
            ->withCount('likes', 'comments')
            ->paginate($pageSize);

        $artworksWithLikedProperty = $artworks->getCollection()->map(function ($artwork) use ($userId) {
            $artwork->liked = $this->isLiked($artwork->id, $userId);
            return $artwork;
        });

        $artworks->setCollection($artworksWithLikedProperty);

        return $this->sendSuccess($artworks, 'Artworks retrieved successfully');
    }

    public function comment(Request $request, $artwork){

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        $comment = ArtworkComment::create([
            'artwork_id' => $artwork,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return $this->sendSuccess($comment, 'Comment added successfully.');
    }

    public function editComment(Request $request, $comment){
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        $comment = ArtworkComment::find($comment);
        $comment->content = $request->input('content');
        $comment->save();
        return $this->sendSuccess($comment, 'Comment updated successfully.');
    }


    public function deleteComment($comment){
        $comment = ArtworkComment::find($comment);
        $comment->delete();
        return $this->sendSuccess(null, 'Comment deleted successfully.');
    }

    public function getCategories(){
        $categories = ArtworkCategory::all();
        return $this->sendSuccess($categories, 'Categories retrieved successfully');
    }



    public function getComments($artworkId){
        $comments = ArtworkComment::where('artwork_id', $artworkId)->with('user.profile')->get();
        return $this->sendSuccess($comments, 'comments fetched');
    }

    private function isLiked($artworkId, $userId)
    {
        return ArtworkLike::where('user_id', $userId)->where('artwork_id', $artworkId)->exists();
    }


}
