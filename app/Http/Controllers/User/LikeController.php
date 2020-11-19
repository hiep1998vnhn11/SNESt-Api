<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
    public function handle_like(Post $post)
    {
        $message_success = 'Handle like successfully!';
        if ($post->privacy == 'blocked')
            return $this->sendBlocked();
        else if ($post->privacy == 'private' && $post->user_id != auth()->user()->id)
            return $this->sendForbidden();
        // isLiked return a like if user had liked a post
        $isLiked = $post->likes->where('user_id', auth()->user()->id)->first();
        if (!$isLiked) { // not Liked anyone
            $this->createLike($post, $message_success);
        } else { // liked or had un liked!
            if ($isLiked->status == 1) //liked! => handle unlike
                return $this->unlike($isLiked, $message_success);
            //un liked! => handle like again!
            else return $this->like($isLiked, $message_success);
        }
        return $this->sendRespondSuccess($post->likes->where('user_id', 1), null);
    }

    /**
     * Create a new like field on post.
     *
     * @param  Post $post, $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLike($post, $message)
    {
        $like = new Like;
        $like->user_id = auth()->user()->id;
        $like->post_id = $post->id;
        $like->status = 1;
        $like->save();
        return $this->sendRespondSuccess($like, $message);
    }

    /**
     * Handle unlike => like a Post.
     *
     * @param  Like $like
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function like($like, $message)
    {
        $like->status = 1;
        $like->save();
        return $this->sendRespondSuccess($like, $message);
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
