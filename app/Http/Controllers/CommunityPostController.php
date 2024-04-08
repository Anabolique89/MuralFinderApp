<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class CommunityPostController extends ApiBaseController
{
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize', 10); // Default page size is 10 if not provided
        $posts = Post::with('user')
            ->withCount('likes') // Count the number of likes
            ->withCount('comments') // Count the number of comments
            ->paginate($pageSize);
        return $this->sendSuccess($posts, 'Posts retrieved successfully');
    }

    public function search(Request $request)
    {
        $query = Post::query();

        $searchQuery = $request->get('query'); // Adjust query parameter name if needed
        if ($searchQuery) {
            $query->where(function ($query) use ($searchQuery) {
                $query->where('title', 'like', '%' . $searchQuery . '%')
                    ->orWhere('content', 'like', '%' . $searchQuery . '%');
            }, null, null, 'OR');
        }

        $posts = $query->paginate(15);

        return $this->sendSuccess($posts, "post searched");
    }

    public function show($post)
    {

        $post = Post::with('user')
            ->withCount('likes') // Count the number of likes
            ->withCount('comments') // Count the number of comments
            ->find($post);

        if (!$post) {
            return $this->sendError('No post with such id', 404);
        }
        return $this->sendSuccess($post, 'post retrieved successfully');
    }

    public function postsByUser($userId)
{
    try {
        $userPosts = Post::with('user')
            ->withCount('likes')
            ->withCount('comments')
            ->where('user_id', $userId)
            ->paginate(10);

        return $this->sendSuccess($userPosts, 'Posts by user retrieved successfully');
    } catch (\Exception $e) {
        \Log::error('Error fetching posts by user: ' . $e->getMessage());
        return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'feature_image' => 'nullable|file|image|max:2048',
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }


            $imagePath = '';
            if ($request->has('feature_image')) {
                $imagePath = $this->uploadImage($request->file('feature_image'), 'posts', 'public');
                if (!$imagePath) {
                    return $this->sendError('Failed to upload image');
                }
            }

            $data = $validator->validated();
            $data['feature_image'] = $imagePath;
            $data['user_id'] = Auth::id();

            $post = post::create($data);
            return $this->sendSuccess($post, 'post created successfully');
        } catch (\Exception $e) {
            Log::error('Error storing post: ' . $e->getMessage());
            return $this->sendError('An error occurred while creating post', 500);
        }
    }

    public function update(Request $request, post $post)
    {

        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'feature_image' => 'nullable|file|image|max:2048',
                'content' => 'required',
            ]);


            if ($validator->fails()) {
                return $this->sendError($validator->errors()->toArray());
            }
            if ($post->user_id !== Auth::id()) {
                return $this->sendError("Can not update another persons post");
            }

            $data = $validator->validated();



            // TODO: make sure 
            if ($request->hasFile('feature_image')) {
                if ($post->feature_image !== null) {
                    Storage::disk('public')->delete($post->feature_image);
                }
                $imagePath = $this->uploadImage($request->file('feature_image'), 'posts', 'public');
                if (!$imagePath) {
                    return $this->sendError('Failed to upload image');
                }
                $data['feature_image'] = $imagePath;
            }

            unset($data['feature_image']);


            $post->update($data);
            return $this->sendSuccess($post, 'post updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage());
            return $this->sendError('An error occurred while updating post', 500);
        }
    }
    public function changeFeatureImage(Request $request, post $post)
    {
        $validator = Validator::make($request->all(), [
            'feature_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        try {
            // Delete the existing image
            Storage::disk('public')->delete($post->image_path);

            // Upload the new image
            $imagePath = $this->uploadImage($request->file('feature_image'), 'uploads/posts');

            // Update the post with the new image path
            $post->update(['feature_image' => $imagePath]);

            return $this->sendSuccess($post, 'post image changed successfully');
        } catch (\Exception $e) {
            Log::error('Error changing post image: ' . $e->getMessage());
            return $this->sendError('An error occurred while changing post image', 500);
        }
    }

    public function destroy($id)
    {
        $post = post::find($id);
        if (!$post) {
            return $this->sendError('no post found');
        }

        if ($post->user_id !== Auth::id()) {
            return $this->sendError("Can not delete another persons post");
        }

        try {
            $post->delete();
            return $this->sendSuccess(null, "post deleted");
        } catch (\Exception $e) {
            Log::error("Error Deleting post: " . $e->getMessage());
            return $this->sendError("Internal Server Error, please refresh and try again");
        }
    }

    public function like(post $post)
    {
        $userId = Auth::id();


        // Check if the user has already liked the post
        $existingLike = PostLike::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->first();

        if ($existingLike) {
            return $this->sendError('You have already liked this post');
        }

        try {
            // Create a new like
            $like = PostLike::create([
                'user_id' => $userId,
                'post_id' => $post->id,
            ]);

            return $this->sendSuccess($like, 'post liked successfully');
        } catch (\Exception $e) {
            Log::error('Error liking post: ' . $e->getMessage());
            return $this->sendError('An error occurred while liking post');
        }
    }

    public function unlike(post $post)
    {
        $userId = Auth::id();

        // Find the like to delete
        $like = PostLike::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->first();

        if (!$like) {
            return $this->sendError('You have not liked this post', 400);
        }

        try {
            // Delete the like
            $like->delete();

            return $this->sendSuccess(null, 'post unliked successfully');
        } catch (\Exception $e) {
            Log::error('Error unliking post: ' . $e->getMessage());
            return $this->sendError('An error occurred while unliking post', 500);
        }
    }

    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray());
        }

        try {
            // Create a new comment
            $comment = PostComment::create([
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'content' => $request->input('content'),
            ]);

            return $this->sendSuccess($comment, 'Comment added successfully');
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            return $this->sendError('An error occurred while adding comment', 500);
        }
    }

    public function getPostComments($post)
    {
        try {
            $comments = PostComment::with('user')->where('post_id', $post)->get();
            return $this->sendSuccess($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error retrieving comments: ' . $e->getMessage());
            return $this->sendError('An error occurred while retrieving comments', 500);
        }
    }
}