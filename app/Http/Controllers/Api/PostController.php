<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostIndexRequest;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(PostIndexRequest $request)
    {
        $perPage = $request->input('per_page', 10);

        $posts = Post::select('id', 'title', 'content', 'user_id', 'created_at')
            ->with([
                'user:id,name',
                'comments:id,user_id,post_id,content,created_at',
                'comments.user:id,name',
                'images:id,post_id,image'
            ])
            ->latest()
            ->paginate($perPage);

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'There are no posts yet',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Posts fetched successfully',
            'data' => $posts
        ], 200);
    }
    public function show(Post $post)
    {
        $post->loadMissing([
            'user:id,name',
            'comments',
            'images'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post fetched successfully',
            'data' => $post
        ], 200);
    }
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $uploadedImages = [];
        try {
            $post = DB::transaction(function () use ($validated, $request, &$uploadedImages) {

                $postCreated = Post::create([
                    ...$validated,
                    'user_id' => $request->user()->id
                ]);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('posts', 'public');
                        $uploadedImages[] = $path;
                        $postCreated->images()->create(['image' => $path]);
                    }
                }


                return $postCreated;

            });
        } catch (\Throwable $e) {
            foreach ($uploadedImages as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully',
            'data' => $post->load('images'),
        ], 201);
    }

}
