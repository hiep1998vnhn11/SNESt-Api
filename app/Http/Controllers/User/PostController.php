<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Support\Arr;
use App\Http\Services\PostService;
use App\Models\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    private $postService;

    public function __construct(PostService $postService)
    {
        $this->middleware('auth:api', ['except' => ['get']]);
        $this->postService = $postService;
    }
    /**
     * Create a new Post.
     *
     * @param  PostRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(PostRequest $request)
    {
        if (!$request->content && !$request->hasFile('files')) {
            return $this->sendRespondError(
                $request,
                'Content or Image is required!',
                config('const.STATUS_CODE_UN_PROCESSABLE')
            );
        }
        $post = new Post();
        $post->user_id = auth()->user()->id;
        $post->content = $request->content;
        if ($request->image_count) $post->image_count = $request->image_count;
        if ($request->privacy) $post->privacy = $request->privacy;
        else $post->privacy = 'public';
        $post->save();
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $uploadFolder = 'files/' . auth()->user()->url . '/' . Carbon::now()->format('Y-m-d');
            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $image_photo_path = $file->storeAs($uploadFolder, $fileName, 's3');
                Storage::disk('s3')->setVisibility($image_photo_path, 'public');
                $path = Storage::disk('s3')->url($image_photo_path);
                $image = new Image();
                $image->post_id = $post->id;
                $image->path = $path;
                $image->save();
            }
        }
        $post->images;
        $post->user;
        return $this->sendRespondSuccess(
            $post,
            'Create post successfully!'
        );
    }

    /**
     * Delete a Post.
     *
     * @param  Post $post
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Post $post)
    {
        if ($this->checkAuthUser($post->user_id))
            return $this->sendForbidden();
        $post->delete();
        return $this->sendRespondSuccess(
            $post,
            'Delete post successfully!'
        );
    }

    /**
     * Update a Post.
     *
     * @param  Post $post PostRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Post $post, PostRequest $request)
    {
        if ($this->checkAuthUser($post->user_id))
            return $this->sendForbidden();
        $message = 'Updated ';
        if ($request->content) {
            $post->content = $request->content;
            $message .= 'content, ';
        }
        if ($request->image_count && $request->image_count != $post->image_count) {
            $post->image_count = $request->image_count;
            $message .= 'image, ';
        }
        if ($request->privacy != $post->privacy) {
            $post->privacy = $request->privacyl;
            $message .= 'privacy, ';
        }
        $post->save();
        return $this->sendRespondSuccess(
            $post,
            $message
        );
    }

    /**
     * Get a Post.
     *
     * @param  Post
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Post $post)
    {
        $post->user;
        $post->loadCount(['likes' => function ($query) {
            $query->where('status', 1);
        }]);
        $likes = $post->likes->where('status', 1);
        foreach ($likes as $like) {
            $like->user;
            if (auth()->user() && $like->user_id == auth()->user()->id) {
                $post->isLiked = true;
            }
        }
        if (auth()->user() && !$post->isLiked) $post->isLiked = false;
        $post->loadCount('comments');
        $comments = $post->comments;
        foreach ($comments as $comment) {
            $comment->loadCount('sub_comments');
            $comment->user;
            if ($comment->sub_comments_count != 0) {
                $post->comments_count += $comment->sub_comments_count;
                $sub_comments = $comment->sub_comments;
                foreach ($sub_comments as $sub_comment)
                    $sub_comment->user;
            }
        }
        return $this->sendRespondSuccess(
            $post,
            'Get Post successfully!'
        );
    }
    public function getComment(Post $post)
    {
        $comments = $post->comments;
        foreach ($comments as $comment) {
            $comment->user;
            $comment->loadCount('sub_comments');
            $comment->sub_comments;
            if ($comment->sub_comments_count != 0) {
                $sub_comments = $comment->sub_comments;
                foreach ($sub_comments as $sub_comment)
                    $sub_comment->user;
            }
        }
        return $this->sendRespondSuccess($comments, 'Get comment successfully!');
    }

    public function store(StorePostRequest $request)
    {
        $data = $this->postService->getPostForAuth($request->all());
        if ($data) return $this->sendRespondSuccess($data, 'Get Post successfully!');
        else return $this->sendRespondError(null, null);
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('files')) return 'true';
        $files = $request->file('files');
        $uploadFolder = 'files/' . auth()->user()->url . '/' . Carbon::now()->format('Y-m-d');

        $paths = '';
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path =
                env('APP_URL') . '/storage/' . $file->storeAs($uploadFolder, $fileName, 'public');
            $paths = $paths . $path;
        }
        return $paths;
    }
}
