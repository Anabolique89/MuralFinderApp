<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ArtworkLike;
use App\Models\ArtworkComment;

class ArtworkController extends ApiBaseController
{
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize', 10); // Default page size is 10 if not provided

        $artworks = Artwork::with(['category', 'user.profile'])
            ->withCount('likes')
            ->withCount('comments')
            ->paginate($pageSize); // Perform pagination before grouping

        // Grouping by category name using collection methods
        $groupedArtworks = $artworks->groupBy(function ($artwork) {
            return $artwork->category ? $artwork->category->name : 'others';
        });

        // Transform grouped artworks to include pagination information
        $groupedArtworksWithPagination = [];

        foreach ($groupedArtworks as $category => $items) {
            $groupedArtworksWithPagination[] = [
                'category' => $category,
                'artworks' => $items,
                'total' => $artworks->total(),
                'current_page' => $artworks->currentPage(),
                'last_page' => $artworks->lastPage()
            ];
        }

        return $this->sendSuccess($groupedArtworksWithPagination, 'Artworks grouped by category retrieved successfully');
    }


    public function search(Request $request)
    {
        try {
            $query = Artwork::with(['category', 'user']);
            $searchQuery = $request->get('query');

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

            $groupedArtworks = $artworks->groupBy('category.title');

            $groupedArtworksWithPagination = [];
            foreach ($groupedArtworks as $category => $items) {
                $groupedArtworksWithPagination[] = [
                    'category' => $category,
                    'artworks' => $items,
                    'total' => $artworks->total(),
                    'current_page' => $artworks->currentPage(),
                    'last_page' => $artworks->lastPage()
                ];
            }

            return $this->sendSuccess($groupedArtworksWithPagination, "Artworks searched");
        } catch (\Exception $e) {
            Log::error('Error in search function: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for artworks.'
            ], 500);
        }
    }
    public function getUserArtworks($userId, Request $request)
    {
        $pageSize = $request->query('pageSize', 10); // Default page size is 10 if not provided

        $artworks = Artwork::with(['category', 'user.profile'])
            ->withCount('likes')
            ->withCount('comments')
            ->where('user_id', $userId)
            ->paginate($pageSize);

        // Grouping by category name using collection methods
        $groupedArtworks = $artworks->groupBy(function ($artwork) {
            return $artwork->category ? $artwork->category->name : 'others';
        });

        // Transform grouped artworks to include pagination information
        $groupedArtworksWithPagination = [];

        foreach ($groupedArtworks as $category => $items) {
            $groupedArtworksWithPagination[] = [
                'category' => $category,
                'artworks' => $items,
                'total' => $artworks->total(),
                'current_page' => $artworks->currentPage(),
                'last_page' => $artworks->lastPage()
            ];
        }

        return $this->sendSuccess($groupedArtworksWithPagination, 'User artworks retrieved successfully');
    }


    public function show($artwork)
    {

        $artwork = Artwork::with('user.profile', 'category')
            ->withCount('likes') // Count the number of likes
            ->withCount('comments') // Count the number of comments
            ->find($artwork);

        if (!$artwork) {
            return $this->sendError('No artwork with such id', 404);
        }
        return $this->sendSuccess($artwork, 'Artwork retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            // Validate input including category_id and image
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
            $artworkData['image_path'] = 'null';

            // Log to check if category_id is being set correctly
            Log::info('Artwork category ID:', ['artwork_category_id' => $artworkData['artwork_category_id']]);

            // Create the artwork
            $artwork = Artwork::create($artworkData);

            $imagePaths = [];

            // Handle image upload if files were uploaded
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $this->uploadImage($image, 'artworks', 'public');
                    if (!$imagePath) {
                        return $this->sendError('Failed to upload image');
                    }
                    $imagePaths[] = $imagePath;
                }
                // Update the artwork's image path
                $artwork->image_path = $imagePaths[0];
                $artwork->save();
            }

            $artwork->refresh();
            // Associate the image paths with the artwork
            foreach ($imagePaths as $imagePath) {
                $artworkImage = new ArtworkImage(['artwork_url' => $imagePath]);
                $artwork->images()->save($artworkImage);
            }

            $artwork->load('images');

            return $this->sendSuccess($artwork, 'Artwork created successfully');
        } catch (\Exception $e) {
            Log::error('Error storing artwork: ' . $e->getMessage());
            return $this->sendError('An error occurred while creating artwork', 500);
        }
    }


    public function update(Request $request, Artwork $artwork)
    {

        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'image' => 'nullable|image|max:2048', // Validate image upload

            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }
            if ($artwork->user_id !== Auth::id()) {
                return $this->sendError("Can not update another persons artwork");
            }

            $data = $validator->validated();



            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($artwork->image_path);
                $imagePath = $this->uploadImage($request->file('image'), 'artworks', 'public');
                if (!$imagePath) {
                    return $this->sendError('Failed to upload image');
                }
                $data['image_path'] = $imagePath;
            }

            unset($data['image']);
            $data['artwork_category_id'] = $request->category_id ?? null;


            $artwork->update($data);
            return $this->sendSuccess($artwork, 'Artwork updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating artwork: ' . $e->getMessage());
            return $this->sendError('An error occurred while updating artwork', 500);
        }
    }
    public function changeImage(Request $request, Artwork $artwork)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        try {
            // Delete the existing image
            Storage::disk('public')->delete($artwork->image_path);

            // Upload the new image
            $imagePath = $this->uploadImage($request->file('image'), 'uploads/artworks');

            // Update the artwork with the new image path
            $artwork->update(['image_path' => $imagePath]);

            return $this->sendSuccess($artwork, 'Artwork image changed successfully');
        } catch (\Exception $e) {
            Log::error('Error changing artwork image: ' . $e->getMessage());
            return $this->sendError('An error occurred while changing artwork image', 500);
        }
    }

    public function destroy($id)
    {
        $artwork = Artwork::find($id);
        if (!$artwork) {
            return $this->sendError('no artwork found');
        }

        if ($artwork->user_id !== Auth::id()) {
            return $this->sendError("Can not delete another persons artwork");
        }

        try {
            $artwork->delete();
            return $this->sendSuccess(null, "artwork deleted");
        } catch (\Exception $e) {
            Log::error("Error Deleting Artwork: " . $e->getMessage());
            return $this->sendError("Internal Server Error, please refresh and try again");
        }
    }

    public function like(Artwork $artwork)
    {
        $userId = Auth::id();


        // Check if the user has already liked the artwork
        $existingLike = ArtworkLike::where('user_id', $userId)
            ->where('artwork_id', $artwork->id)
            ->first();

        if ($existingLike) {
            return $this->sendError('You have already liked this artwork');
        }

        try {
            // Create a new like
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

        // Find the like to delete
        $like = ArtworkLike::where('user_id', $userId)
            ->where('artwork_id', $artwork->id)
            ->first();

        if (!$like) {
            return $this->sendError('You have not liked this artwork', 400);
        }

        try {
            // Delete the like
            $like->delete();

            return $this->sendSuccess(null, 'Artwork unliked successfully');
        } catch (\Exception $e) {
            Log::error('Error unliking artwork: ' . $e->getMessage());
            return $this->sendError('An error occurred while unliking artwork', 500);
        }
    }

    public function comment(Request $request, Artwork $artwork)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        try {
            // Create a new comment
            $comment = ArtworkComment::create([
                'user_id' => Auth::id(),
                'artwork_id' => $artwork->id,
                'content' => $request->input('content'),
            ]);

            return $this->sendSuccess($comment, 'Comment added successfully');
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            return $this->sendError('An error occurred while adding comment', 500);
        }
    }

    public function getCategories()
    {
        return $this->sendSuccess(ArtworkCategory::all(), 'categories fetch');
    }

    public function getComments($artworkId)
    {
        try {
            $comments = ArtworkComment::with('user.profile')
                ->where('artwork_id', $artworkId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->sendSuccess($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching comments: ' . $e->getMessage());
            return $this->sendError('An error occurred while fetching comments', 500);
        }
    }


}
