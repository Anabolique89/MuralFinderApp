<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Wall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class WallController extends ApiBaseController
{
    /**
     * Display a listing of the walls.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $walls = Wall::paginate($pageSize);
        return $this->sendSuccess($walls);
    }

    /**
     * Store a newly created wall in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'location_text' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'is_verified' => 'required|boolean',
                'image' => 'required|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }

            // Handle image upload
            $imagePath = $this->uploadImage($request->file('image'), 'walls', 'public');

            if (!$imagePath) {
                return $this->sendError('Failed to upload image.', 500);
            }

            // Create the wall
            $wall = Wall::create([
                'location_text' => $request->location_text,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_verified' => $request->is_verified ? True : False,
                'image_path' => $imagePath,
                'added_by' => Auth::id()
            ]);

            return $this->sendSuccess($wall, 'Wall created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while storing the wall.'. $e->getMessage(),500);
        }
    }
    /**
     * Display the specified wall.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wall = Wall::with('addedBy')->findOrFail($id);
        return $this->sendSuccess($wall);
    }

    /**
     * Update the specified wall in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'location_text' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'is_verified' => 'boolean',
            'image' => 'image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        $wall = Wall::findOrFail($id);


        if ($request->hasFile('image')) {
            // Delete previous image
            if ($wall->image_path) {
                $this->deleteImage($wall->image_path);
            }
            $imagePath = $this->uploadImage($request->file('image'));
            if (!$imagePath) {
                return $this->sendError('Failed to upload image.', 500);
            }
            $wall->image_path = $imagePath;
        }

        // Update wall attributes
        $wall->fill($request->except('image'))->save();

        return $this->sendSuccess($wall, 'Wall updated successfully.');
    }

    /**
     * Remove the specified wall from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wall = Wall::findOrFail($id);

        // Delete the associated image
        if ($wall->image_path) {
            $this->deleteImage($wall->image_path);
        }

        $wall->delete();

        return $this->sendSuccess(null, 'Wall deleted successfully.');
    }

    /**
     * Verify the specified wall.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verifyWall($id)
    {
        $wall = Wall::findOrFail($id);

        // Mark the wall as verified
        $wall->update(['is_verified' => true]);

        return $this->sendSuccess($wall, 'Wall verified successfully.');
    }

    public function search(Request $request)
    {
        $query = Wall::query();

        if ($request->has('location_text')) {
            $query->where('location_text', 'like', '%' . $request->location_text . '%');
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        if ($request->has('max_rating')) {
            $query->where('rating', '<=', $request->max_rating);
        }

        $walls = $query->paginate(10);

        return $this->sendSuccess($walls);
    }

}
