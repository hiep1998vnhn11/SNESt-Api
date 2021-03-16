<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Create comment on a post.
     *
     * @param  Post $post, 
     * 
     * @param  CommentRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Post $post, CommentRequest $request)
    {
        if (!$request->content && !$request->image_path)
            return $this->sendRespondError(
                $request,
                'Content or image is required!',
                config('const.STATUS_CODE_UN_PROCESSABLE')
            );
        if ($post->privacy == 'blocked')
            return $this->sendBlocked();
        else if ($post->privacy == 'private' && $post->user_id != auth()->user()->id)
            return $this->sendForbidden();
        $comment = new Comment();
        if ($request->content)
            $comment->content = $request->content;
        if ($request->image_path)
            $comment->image_path = $request->image_path;
        $comment->post_id = $post->id;
        $comment->user_id = auth()->user()->id;
        $comment->save();
        return $this->sendRespondSuccess(
            $comment,
            'Create comment successfully!'
        );
    }

    /**
     * Handle delete a comment.
     *
     * @param  Comment $comment, 
     * 
     * @param  CommentRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Comment $comment)
    {
        if ($this->checkAuthUser($comment->user_id) || $this->checkAuthUser($comment->post->user_id))
            return $this->sendForbidden();
        $comment->delete();
        return $this->sendRespondSuccess($comment, 'Delete comment successfully!');
    }

    /**
     * Handle update a comment.
     *
     * @param  Comment $comment, 
     * 
     * @param  CommentRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Comment $comment, CommentRequest $request)
    {
        if ($this->checkAuthUser($comment->user_id))
            return $this->sendForbidden();

        if ($request->content)
            $comment->content = $request->content;
        if ($request->image_path)
            $comment->image_path = $request->image_path;
        $comment->save();
        return $this->sendRespondSuccess($comment, 'Update comment successfully!');
    }

    public function getSubComment(Comment $comment)
    {
        $subComments = $comment->sub_comments()
            ->with('user')
            ->with('likeStatus')
            ->with('likes', function ($like) {
                $like->where('status', '>', 0);
            })
            ->get();
        return $this->sendRespondSuccess($subComments, 'Get sub comment successfully!');
    }

    public function getSubCommentGuest(Comment $comment)
    {
        $subComments = $comment->sub_comments()
            ->with('user')
            ->with('likes', function ($like) {
                $like->where('status', '>', 0);
            })
            ->get();
        return $this->sendRespondSuccess($subComments, 'Get sub comment successfully!');
    }
}
