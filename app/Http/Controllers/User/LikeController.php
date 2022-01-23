<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLikeRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\Notification;
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
    public function handleLike($pid, HandleLikeRequest $request)
    {
        $post = Post::where('uid', $pid)->firstOrFail();
        $requestStatus = $request->status;
        $type = 'post';
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
        $type = 'comment';
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
    private function createLike($id, $status, $type = 'App\Models\Post')
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->likeable_type = $type;
        $like->likeable_id = $id;
        $like->status = intval($status);
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
    private function like($like, $status)
    {
        $like->status = intval($status);
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
    private function unlike($like, $message)
    {
        $like->status = 0;
        $like->save();
        return $like;
    }

    private function sendLikeNotificationToUser($post)
    {
        $likes_count = Like::where('likeable_type', 'post')
            ->where('likeable_id', $post->id)
            ->where('status', '>', 0)
            ->count();
        $notification = $post->user->notifications()
            ->where('type', 'post')
            ->where('object_type', 'post')
            ->where('object_id', $post->id)
            ->where('object_url', $post->uid)
            ->first();
        if ($notification) {
            $notification->title = 'và ' . $likes_count . ' người khác đã thả biểu tượng cảm xúc vào bài viết của bạn';
            $notification->read_at = null;
            $notification->seen_at = null;
            $notification->created_at = now();
            $notification->target_user_id = auth()->user()->id;
            return $notification->save();
        }
        $notification = new Notification();
        $notification->title = 'và ' . $likes_count . ' người khác đã thả biểu tượng cảm xúc vào bài viết của bạn';
        $notification->read_at = null;
        $notification->seen_at = null;
        $notification->target_user_id = auth()->user()->id;
        $notification->object_type = 'post';
        $notification->object_id = $post->id;
        $notification->object_url = $post->uid;
        $notification->type = 'post';
        $notification->user_id = $post->user->id;
        return $notification->save();
    }
}
