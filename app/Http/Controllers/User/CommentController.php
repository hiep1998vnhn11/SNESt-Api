<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function store($pid, CommentRequest $request)
    {
        $post = Post::where('uid', $pid)->firstOrFail();
        if (!$request->content && !$request->hasFile('image'))
            return $this->sendRespondError(
                $request,
                'Content or image is required!',
                config('const.STATUS_CODE_UN_PROCESSABLE')
            );
        if ($post->privacy == 'blocked') return $this->sendBlocked();
        if ($post->privacy == 'private' && $post->user_id != auth()->user()->id)
            return $this->sendForbidden();
        $comment = new Comment();
        if ($request->content)
            $comment->content = $request->content;
        if ($request->hasFile('image')) {
            $uploadFolder = 'public/files/posts/' . $post->id;
            $file = $request->file('image');
            $name = time() . '_' . $file->getClientOriginalName();
            $type = $file->getClientOriginalExtension();
            $file_path = $file->storeAs($uploadFolder, $name);
            $path = Storage::disk('local')->url($file_path);
            $comment->image_path = config('app.url') .  $path;
        }
        $comment->post_id = $post->id;
        $comment->user_id = auth()->user()->id;
        $comment->save();
        $this->sendCommentNotificationToUser($post);
        // $comment->user = auth()->user();
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
            ->withCount('likes')
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

    private function sendCommentNotificationToUser($post)
    {
        $user = $post->user;
        $notification = $user->notifications()
            ->where('type', 'App\Notifications\CommentNotification')
            ->where('data->type', 'post')
            ->where('data->id', $post->uid)
            ->first();
        if ($notification) {
            $notification->data = [
                'username' => auth()->user()->full_name,
                'image' => auth()->user()->profile_photo_path,
                'type' => 'post',
                'id' => $post->uid
            ];
            $notification->updated_at = Carbon::now();
            $notification->read_at = null;
            $notification->save();
            return;
        }
        $user->notify(new CommentNotification([
            'username' => auth()->user()->full_name,
            'type' => 'post',
            'id' => $post->uid,
            'image' => auth()->user()->profile_photo_path,
        ]));
    }
}
