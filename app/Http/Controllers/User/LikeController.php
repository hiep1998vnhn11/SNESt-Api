<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLikeRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\SubComment;
use App\Notifications\LikeNotification;
use Carbon\Carbon;

class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Handle like a Post.
     *
     * @param  Post $post
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleLike(Post $post, HandleLikeRequest $request)
    {
        $requestStatus = $request->status;
        $type = 'App\Models\Post';
        if ($post->privacy == 'blocked') return $this->sendBlocked();
        if ($post->privacy == 'private' && $post->user_id != auth()->user()->id)
            return $this->sendForbidden();
        // isLiked return a like if user had liked a post
        $isLikeCreated = $post->likes()
            ->where('user_id', auth()->user()->id)
            ->first();
        if (!$isLikeCreated) {
            //Nếu chưa like lần nào tiến hành tạo mới like
            $isLikeCreated = $this->createLike($post->id, $requestStatus, $type);
        } else if ($isLikeCreated->status == $requestStatus) {
            // Nếu like status bằng với status mà request gửi lên, tiến hành unlike
            $isLikeCreated = $this->like($isLikeCreated, 0, $type);
        } else $isLikeCreated = $this->like($isLikeCreated, $requestStatus, $type);
        // Còn lại thì tiến hành chuyển đã like status về status request gửi lên
        if ($isLikeCreated->status != 0 && $post->user->id != auth()->user()->id) {
            $this->sendLikeNotificationToUser($post);
        }
        return $this->sendRespondSuccess($isLikeCreated, null);
    }

    public function handleLikeComment(Comment $comment, HandleLikeRequest $request)
    {
        $requestStatus = $request->status;
        $type = 'App\Models\Comment';
        // isLiked return a like if user had liked a post
        $isLikeCreated = $comment->likes()
            ->where('user_id', auth()->user()->id)
            ->first();
        if (!$isLikeCreated) {
            //Nếu chưa like lần nào tiến hành tạo mới like
            $isLikeCreated = $this->createLike($comment->id, $requestStatus, $type);
        } else if ($isLikeCreated->status == $requestStatus) {
            // Nếu like status bằng với status mà request gửi lên, tiến hành unlike
            $isLikeCreated = $this->like($isLikeCreated, 0, $type);
        } else $isLikeCreated = $this->like($isLikeCreated, $requestStatus, $type);
        // Còn lại thì tiến hành chuyển đã like status về status request gửi lên
        // if ($isLikeCreated->status != 0 && $comment->user->id != auth()->user()->id) {
        //     $this->sendLikeNotificationToUser($comment);
        // }
        return $this->sendRespondSuccess($isLikeCreated, null);
    }

    public function handle_like_sub_comment(SubComment $sub_comment, HandleLikeRequest $request)
    {
        $requestStatus = $request->status;
        $type = 'App\Models\SubComment';
        // isLiked return a like if user had liked a post
        $isLikeCreated = $sub_comment->likes()
            ->where('user_id', auth()->user()->id)
            ->first();
        if (!$isLikeCreated) {
            //Nếu chưa like lần nào tiến hành tạo mới like
            $isLikeCreated = $this->createLike($sub_comment->id, $requestStatus, $type);
        } else if ($isLikeCreated->status == $requestStatus) {
            // Nếu like status bằng với status mà request gửi lên, tiến hành unlike
            $isLikeCreated = $this->like($isLikeCreated, 0, $type);
        } else $isLikeCreated = $this->like($isLikeCreated, $requestStatus, $type);
        // Còn lại thì tiến hành chuyển đã like status về status request gửi lên
        // if ($isLikeCreated->status != 0 && $sub_comment->user->id != auth()->user()->id) {
        //     $this->sendLikeNotificationToUser($sub_comment);
        // }
        return $this->sendRespondSuccess($isLikeCreated, null);
    }

    /**
     * Create a new like field on post.
     *
     * @param  Post $post
     * @param  Digit $status
     * @param  StringModels $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLike($id, $status, $type = 'App\Models\Post')
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->likeable_type = $type;
        $like->likeable_id = $id;
        $like->status = $status;
        $like->save();
        return $like;
    }

    /**
     * Handle unlike => like a Post.
     *
     * @param  Like $like
     *  @param  Digit $status
     *  @param  String $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function like($like, $status)
    {
        $like->status = $status;
        $like->save();
        return $like;
    }

    /**
     * Handle like =>unlike a Post.
     *
     * @param  Like $like
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlike($like, $message)
    {
        $like->status = 0;
        $like->save();
        return $like;
    }

    public function sendLikeNotificationToUser($post)
    {
        $likes_count = Like::where('likeable_type', 'App\Models\Post')
            ->where('likeable_id', $post->id)
            ->where('status', '>', 0)
            ->count();
        $notification = $post->user->notifications()
            ->where('type', 'App\Notifications\LikeNotification')
            ->where('data->type', 'post')
            ->where('data->id', $post->uid)
            ->first();
        if ($notification) {
            $notification->data = [
                'username' => auth()->user()->full_name,
                'image' => auth()->user()->profile_photo_path,
                'likes_count' => $likes_count,
                'type' => 'post',
                'id' => $post->uid
            ];
            $notification->updated_at = Carbon::now();
            $notification->read_at = null;
            $notification->save();
            return;
        }
        $notification = $post->user->notify(new LikeNotification([
            'username' => auth()->user()->full_name,
            'type' => 'post',
            'id' => $post->uid,
            'image' => auth()->user()->profile_photo_path,
            'likes_count' => $likes_count
        ]));
    }
}
