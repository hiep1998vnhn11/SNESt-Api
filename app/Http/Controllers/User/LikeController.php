<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLikeRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;

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
    public function handle_like(Post $post, HandleLikeRequest $request)
    {
        $requestStatus = $request->status;
        $message_success = 'Handle like successfully!';
        if ($post->privacy == 'blocked')
            return $this->sendBlocked();
        else if ($post->privacy == 'private' && $post->user_id != auth()->user()->id)
            return $this->sendForbidden();
        // isLiked return a like if user had liked a post
        $isLikeCreated = $post->likes()
            ->where('user_id', auth()->user()->id)
            ->first();
        if (!$isLikeCreated) {
            //Nếu chưa like lần nào tiến hành tạo mới like
            $this->createLike($post, $requestStatus);
        } else if ($isLikeCreated->status == $requestStatus) {
            // Nếu like status bằng với status mà request gửi lên, tiến hành unlike
            return $this->like($isLikeCreated, 0);
        } else return $this->like($isLikeCreated, $requestStatus);
        // Còn lại thì tiến hành chuyển đã like status về status request gửi lên
        return $this->sendRespondSuccess($isLikeCreated, null);
    }

    /**
     * Create a new like field on post.
     *
     * @param  Post $post, $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLike($post, $status)
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->likeable_type = 'App\Models\Post';
        $like->likeable_id = $post->id;
        $like->status = $status;
        $like->save();
        return $this->sendRespondSuccess($like, 'create like success with status like ' . $status);
    }

    /**
     * Handle unlike => like a Post.
     *
     * @param  Like $like
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function like($like, $status)
    {
        $like->status = $status;
        $like->save();
        return $this->sendRespondSuccess($like, 'change like to ' . $status);
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
        return $this->sendRespondSuccess($like, $message);
    }
}
