<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
    public function update(UpdateCommentRequest $request, Post $post, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $comment->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'comment updated successfully',
            'data' => $comment
        ]);
    }
    public function destroy(Post $post, Comment $comment, )
    {
        Gate::authorize('delete', $comment);
        $comment->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'comment deleted successfully'
        ]);
    }
}
