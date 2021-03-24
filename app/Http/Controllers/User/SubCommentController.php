<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\SubComment;
use Illuminate\Http\Request;
use App\Http\Requests\SubCommentRequest;
use Carbon\Carbon;

class SubCommentController extends Controller
{
    /**
     * Create sub comment on a comment.
     *
     * @param  Comment $comment, 
     * 
     * @param  CommentRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Comment $comment, SubCommentRequest $request)
    {
        if ($comment->post->privacy == 'blocked')
            return $this->sendBlocked();
        else if ($comment->post->privacy == 'private' && $comment->post->user_id != auth()->user()->id)
            return $this->sendForbidden();

        $sub_comment = new SubComment();
        $sub_comment->comment_id = $comment->id;
        $sub_comment->user_id = auth()->user()->id;
        $sub_comment->content = $request->content;
        $sub_comment->save();

        $isNotification = false;
        foreach ($comment->user->notifications()->where('type', 'App\Notifications\SubCommentNotification')->get() as $notification) {
            if ($notification->data->comment_id == $comment->id) {
                $notification->data = [
                    'username' => auth()->user()->name,
                    'image' => auth()->user()->profile_photo_path,
                    'comment_id' => $comment->id
                ];
                $notification->updated_at = Carbon::now();
                $notification->read_at = null;
                $notification->save();
                $isNotification = true;
                break;
            }
        }
        if (!$isNotification) {
            $comment->user->notify([
                'username' => auth()->user()->name,
                'image' => auth()->user()->profile_photo_path,
                'comment_id' => $comment->id
            ]);
        }
        return $this->sendRespondSuccess($sub_comment, 'Create sub comment successfully!');
    }

    /**
     * delete a sub comment.
     *
     * @param  SubComment $sub_comment, 
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(SubComment $sub_comment)
    {
        if ($this->checkAuthUser($sub_comment->user_id) || $this->checkAuthUser($sub_comment->comment->post->user_id))
            return $this->sendForbidden();
        $sub_comment->delete();
        return $this->sendRespondSuccess($sub_comment, 'Delete Sub Comment Successfully!');
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
    public function update(SubComment $sub_comment, SubCommentRequest $request)
    {
        if ($this->checkAuthUser($sub_comment->user_id))
            return $this->sendForbidden();
        $sub_comment->content = $request->content;
        $sub_comment->save();
        $this->sendRespondSuccess($sub_comment, 'Update sub comment successfully!');
    }
}
