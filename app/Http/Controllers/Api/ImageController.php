<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(StoreImageRequest $request, Post $post)
    {
        Gate::authorize('manageImages', $post);
        $uploadedImages = [];

        try {

            $createdImages = DB::transaction(function () use (&$uploadedImages, $post, $request) {
                $imagesCreated  = [];

                foreach ($request->file('images') as $image) {
                    $path = $image->store('posts', 'public');

                    $uploadedImages[] = $path;

                    $imagesCreated [] = $post->images()->create(
                        ['image' => $path]
                    );
                }

                return $imagesCreated ;

            });
        } catch (\Throwable $e) {
            foreach ($uploadedImages as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'image add successfully',
            'data' => $createdImages,
        ], 201);

    }
    public function destroy(Post $post, PostImage $image)
    {

        abort_unless($image->post_id === $post->id, 404);
        Gate::authorize('manageImages', $post);
        $path = $image->image;
        $image->delete();
        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable $th) {
            Log::error('error to remove image', [
                'image' => $path,
                'error' => $th->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Image deleted successfully',
        ],200);
    }
}
