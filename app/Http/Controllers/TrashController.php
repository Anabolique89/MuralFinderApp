<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Artwork;
use App\Models\Wall;
use App\Models\User;
use App\Models\Post; // Assuming you have a Post model
use Illuminate\Http\Request;

class TrashController extends ApiBaseController
{
    /**
     * Restore a soft-deleted item.
     *
     * @param  Request  $request
     * @param  string  $model
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request, $model, $id)
    {
        $modelClass = $this->getModelClass($model);
        $item = $modelClass::withTrashed()->findOrFail($id);
        $item->restore();

        return $this->sendSuccess($item, 'Item restored successfully');
    }

    /**
     * Permanently delete a soft-deleted item.
     *
     * @param  Request  $request
     * @param  string  $model
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $model, $id)
    {
        $modelClass = $this->getModelClass($model);
        $item = $modelClass::withTrashed()->findOrFail($id);
        $item->forceDelete();

        return $this->sendSuccess(null, 'Trashed Item Deleted permanently');
    }

    /**
     * Retrieve all trashed items across multiple models.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $artworks = Artwork::onlyTrashed()->get();
        $walls = Wall::onlyTrashed()->get();
        $users = User::onlyTrashed()->get();
        $posts = Post::onlyTrashed()->get(); // Assuming you have a Post model

        return $this->sendSuccess(
            [
                'artworks' => $artworks,
                'walls' => $walls,
                'users' => $users,
                'posts' => $posts,

            ],
            'Trashed Items Fetech'
        );
    }

    /**
     * Get the model class based on the model name.
     *
     * @param  string  $model
     * @return string
     */
    private function getModelClass($model)
    {
        switch ($model) {
            case 'artwork':
                return Artwork::class;
            case 'wall':
                return Wall::class;
            case 'user':
                return User::class;
            case 'post':
                return Post::class;
            default:
                abort(404, 'Model not found');
        }
    }
}
