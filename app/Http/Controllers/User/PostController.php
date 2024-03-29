<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\HandleLikeRequest;
use App\Http\Requests\ImageRequest;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Support\Arr;
use App\Http\Services\PostService;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Image;
use App\Models\Like;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $this->middleware('auth:api')->except(['get', 'store', 'getCommentGuest']);
        $this->postService = $postService;
    }
    /**
     * Create a new Post.
     *
     * @param  PostRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PostRequest $request)
    {
        if (!$request->content && !$request->hasFile('files')) {
            return $this->sendRespondError(
                null,
                'Bạn chưa nhập nội dung hoặc chưa chọn ảnh!',
                config('const.STATUS_CODE_UN_PROCESSABLE')
            );
        }
        $media = null;
        if ($request->media) {
            $media = @json_decode($request->media);
        }
        $post = new Post();
        $post->user_id = auth()->user()->id;
        $post->content = $request->content;
        $id = rand(100000000000, 99999999999999);
        $post->uid = "{$id}";
        if ($request->image_count) $post->image_count = $request->image_count;
        $post->privacy = intval($request->privacy);
        $post->save();
        if ($media) {
            Media::whereIn('id', $media)
                ->where('user_id', auth()->user()->id)
                ->update([
                    'object_id' => $post->id,
                    'object_type' => 'post'
                ]);
        }
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $uploadFolder = 'public/files/' . auth()->user()->url . '/' . Carbon::now()->format('Y-m-d');
            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $image_photo_path = $file->storeAs($uploadFolder, $fileName);
                Storage::disk('local')->setVisibility($image_photo_path, 'public');
                $path = Storage::disk('local')->url($image_photo_path);
                $image = new Image();
                $image->imageable_type = 'App\Models\Post';
                $image->imageable_id = $post->id;
                $image->path = url($path);
                $image->save();
            }
        }
        $post->media = $post->media()->take(4)->get();
        // $post->user = auth()->user();
        $post->comments_count = 0;
        $post->likes = [];
        $post->likeStatus = null;
        $post->likeds_count = 0;
        $post->user_profile_photo_path = auth()->user()->profile_photo_path;
        $post->user_name = auth()->user()->full_name;
        $post->user_url = auth()->user()->url;
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
    public function get(String $post)
    {
        $post = Post::where('uid', $post)
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->select(
                'posts.id',
                'posts.uid',
                'posts.content',
                'posts.updated_at',
                'posts.privacy',
                'posts.created_at',
                'users.full_name as user_name',
                'users.profile_photo_path as user_profile_photo_path',
                'users.url as user_url',
                'posts.image_count'
            )
            ->firstOrFail();
        $post->media;
        $post->likeStatus;
        $post->loadCount(['likes', 'comments']);
        return $this->sendRespondSuccess($post);
    }

    public function getComment(Request $request, String $post)
    {
        $limit = isset($request->limit) ? $request->limit : config('const.DEFAULT_PER_PAGE');
        $offset = isset($request->offset) ? $request->offset : 0;
        $post = Post::where('uid', $post)->firstOrFail();
        $comments = Comment::query()
            ->select('comments.*', 'likes.status as like_status')
            ->where('post_id', $post->id)
            ->withCount(['sub_comments', 'liked'])
            ->leftJoin('likes', function ($join) {
                $join->on('likes.likeable_id', '=', 'comments.id')
                    ->where('likes.likeable_type', '=', 'App\Models\Comment')
                    ->where('likes.user_id', '=', auth()->user()->id);
            })
            ->with(['user'])
            ->orderBy('updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        return $this->sendRespondSuccess($comments);
    }

    public function getLike(Request $request, String $post)
    {
        $limit = isset($request->limit) ? $request->limit : config('const.DEFAULT_PER_PAGE');
        $offset = isset($request->offset) ? $request->offset : 0;
        $post = Post::where('uid', $post)->firstOrFail();
        $likeableType = 'App\Models\Post';
        $likes = Like::query()
            ->where('likes.likeable_type', $likeableType)
            ->where('likes.likeable_id', $post->id)
            ->where('likes.status', '>', 0)
            ->leftJoin('users', 'users.id', 'likes.user_id')
            ->select('likes.*', 'users.full_name as user_name', 'users.profile_photo_path as user_profile_photo_path', 'users.url as user_url')
            ->orderBy('updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $likesCount = Like::query()
            ->where('likes.likeable_type', $likeableType)
            ->where('likes.likeable_id', $post->id)
            ->count();
        return $this->sendRespondSuccess([
            'likes' => $likes,
            'likes_count' => $likesCount
        ]);
    }

    public function getCommentGuest(String $post)
    {
        $post = Post::where('uid', $post)->firstOrFail();
        $comments = $post->comments()->withCount('sub_comments')
            ->with('user')
            ->with('likes', function ($like) {
                $like->where('status', '>', 0)
                    ->with('user');
            })
            ->get();
        return $this->sendRespondSuccess($comments, 'Get comment successfully!');
    }


    public function storePost(StorePostRequest $request)
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
                config('app.url') . '/storage/' . $file->storeAs($uploadFolder, $fileName, 'public');
            $paths = $paths . $path;
        }
        return $paths;
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $offset = Arr::get($params, 'offset', 0);
        $limit = Arr::get($params, 'limit', 5);
        $user = auth()->user();
        $followingList = $user->follows()
            ->where('status', 1)
            ->pluck('followed_id');
        $followingList[] = $user->id;
        $posts = Post::whereIn('posts.user_id', $followingList)
            ->with(['images', 'media'])
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->leftJoin(
                'likes',
                function ($join) {
                    $join->on('likes.likeable_id', '=', 'posts.id')
                        ->where('likes.likeable_type', '=', 'post')
                        ->where('likes.user_id', auth()->user()->id);
                }
            )
            ->select(
                'posts.id',
                'posts.uid',
                'posts.content',
                'posts.updated_at',
                'posts.created_at',
                'posts.privacy',
                'users.full_name as user_name',
                'users.profile_photo_path as user_profile_photo_path',
                'users.url as user_url',
                'posts.image_count',
                'likes.status as like_status',
                DB::raw('(SELECT count(*) FROM comments WHERE posts.id = comments.post_id) as comments_count'),
                DB::raw("(SELECT count(*) FROM likes WHERE posts.id = likes.likeable_id AND likes.likeable_type = 'post' AND likes.status > 0) as likes_count"),
            )
            ->orderBy('updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($query) {
                $query->setRelation('media', $query->media->take(4));
                return $query;
            });
        return $this->sendRespondSuccess($posts);
    }
}
