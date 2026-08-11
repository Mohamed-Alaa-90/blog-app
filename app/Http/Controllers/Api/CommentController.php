<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $comment = $post->comments()->create([
            "user_id" => $request->user()->id,
            'content' => $request->validated('content')
        ]);
        $comment->load('user:id,name');
        return response()->json([
            'status' => 'success',
            'message' => 'comment created successfully',
            'data' => $comment
        ], 201);
    }
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->select('id', 'post_id', 'user_id', 'content', 'created_at')
            ->with('user:id,name')
            ->latest()
            ->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'comment fetched successfully',
            'data' => $comments
        ], 200);
    }
}
