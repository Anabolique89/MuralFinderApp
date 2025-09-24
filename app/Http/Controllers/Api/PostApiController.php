<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\PostService;
use App\Services\CommentService;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostApiController extends ApiBaseController
{
    protected PostService $postService;
    protected CommentService $commentService;
    protected CategoryRepository $categoryRepository;

    public function __construct(
        PostService $postService,
        CommentService $commentService,
        CategoryRepository $categoryRepository
    ) {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get posts feed
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'type', 'featured']);
            $perPage = $request->input('per_page', 15);

            $posts = $this->postService->getPostsFeed($filters, $perPage);

            return $this->sendSuccess($posts, 'Posts retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Posts index error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve posts', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single post
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $post = $this->postService->getPostById($id, $request->user());

            if (!$post) {
                return $this->sendError('Post not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($post, 'Post retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Post show error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve post', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new post
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'type' => ['required', Rule::in(['article', 'discussion', 'question', 'showcase', 'event', 'news'])],
            'tags' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'is_published' => 'boolean',
            'publish_at' => 'nullable|date|after:now',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $postData = $request->only([
                'title', 'content', 'excerpt', 'category_id', 'type', 'tags',
                'is_published', 'publish_at', 'meta_title', 'meta_description',
                'allow_comments', 'is_featured'
            ]);

            $images = $request->file('images', []);
            $featuredImage = $request->file('featured_image');

            $post = $this->postService->createPost($user, $postData, $images, $featuredImage);

            return $this->sendSuccess($post, 'Post created successfully', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Post creation error: ' . $e->getMessage());
            return $this->sendError('Failed to create post', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update post
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'type' => ['sometimes', 'required', Rule::in(['article', 'discussion', 'question', 'showcase', 'event', 'news'])],
            'tags' => 'nullable|array',
            'is_published' => 'boolean',
            'publish_at' => 'nullable|date|after:now',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $postData = $request->only([
                'title', 'content', 'excerpt', 'category_id', 'type', 'tags',
                'is_published', 'publish_at', 'meta_title', 'meta_description',
                'allow_comments', 'is_featured'
            ]);

            $updatedPost = $this->postService->updatePostById($id, $user, $postData);

            if (!$updatedPost) {
                return $this->sendError('Post not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($updatedPost, 'Post updated successfully');
        } catch (\Exception $e) {
            logger()->error('Post update error: ' . $e->getMessage());
            return $this->sendError('Failed to update post', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete post
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $success = $this->postService->deletePostById($id, $user);

            if (!$success) {
                return $this->sendError('Post not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            logger()->error('Post deletion error: ' . $e->getMessage());
            return $this->sendError('Failed to delete post', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Like/Unlike post
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->postService->toggleLike($id, $user);

            if (!$result) {
                return $this->sendError('Post not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Post like toggle error: ' . $e->getMessage());
            return $this->sendError('Failed to toggle like', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get post comments
     */
    public function comments(Request $request, int $id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $comments = $this->postService->getPostComments($id, $perPage);

            if ($comments === null) {
                return $this->sendError('Post not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Post comments error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve comments', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add comment to post
     */
    public function addComment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $comment = $this->postService->addComment(
                $id,
                $user,
                $request->content,
                $request->parent_id
            );

            if (!$comment) {
                return $this->sendError('Post not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comment, 'Comment added successfully', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Post comment creation error: ' . $e->getMessage());
            return $this->sendError('Failed to add comment', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search posts
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,id',
            'type' => ['nullable', Rule::in(['article', 'discussion', 'question', 'showcase', 'event', 'news'])],
            'user_id' => 'nullable|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['category_id', 'type', 'user_id']);
            $perPage = $request->input('per_page', 15);

            $posts = $this->postService->searchPosts($query, $filters, $perPage);

            return $this->sendSuccess($posts, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Post search error: ' . $e->getMessage());
            return $this->sendError('Search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get featured posts
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $posts = $this->postService->getFeaturedPosts($limit);

            return $this->sendSuccess($posts, 'Featured posts retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Featured posts error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve featured posts', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trending posts
     */
    public function trending(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $posts = $this->postService->getTrendingPosts($limit);

            return $this->sendSuccess($posts, 'Trending posts retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Trending posts error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve trending posts', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
