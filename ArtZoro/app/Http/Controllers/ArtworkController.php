<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ArtworkController extends ApiBaseController
{
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize', 10); // Default page size is 10 if not provided
        $artworks = Artwork::with('user')->paginate($pageSize);
        return $this->sendSuccess($artworks, 'Artworks retrieved successfully');
    }

    public function show($artwork)
    {

        $artwork = Artwork::find($artwork);

        if (!$artwork) {
            return $this->sendError('No artwork with such id', 404);
        }
        return $this->sendSuccess($artwork, 'Artwork retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'image' => 'required|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }

            $imagePath = $this->uploadImage(file: $request->file('image'), folder: 'uploads/artworks', disk: 'public');
            if (!$imagePath) {
                return $this->sendError('Failed to upload image');
            }

            $data = $validator->validated();
            $data['image_path'] = $imagePath;
            $data['user_id'] = Auth::id();

            $artwork = Artwork::create($data);
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
                'description' => 'required|string',
                'image' => 'nullable|image|max:2048', // Validate image upload
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }

            $data = $validator->validated();

            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request->file('image'), 'uploads/artworks');
                if (!$imagePath) {
                    return $this->sendError('Failed to upload image');
                }
                $data['image_path'] = $imagePath;
            }

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

    public function destroy($id){
        $artwork = Artwork::find($id);
        if(!$artwork){
            return $this->sendError('no artwork found');
        }

        if($artwork->user_id !== Auth::id()){
            return $this->sendError("Can not delete another persons artwor");
        }

        try{
            $artwork->delete();
            return $this->sendSuccess(null, "artwork deleted");
        }catch(\Exception $e){
            Log::error("Error Deleting Artwork: " . $e->getMessage());
            return $this->sendError("Internal Server Error, please refresh and try again");
        }
    }

}
