<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\SubComment;
use Illuminate\Http\Request;
use App\Http\Requests\SubCommentRequest;
use App\Notifications\SubCommentNotification;
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
        $sub_comment = new SubComment();
        $sub_comment->comment_id = $comment->id;
        $sub_comment->user_id = auth()->user()->id;
        $sub_comment->content = $request->content;
        $sub_comment->save();
        $this->sendCommentNotificationToUser($comment);
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

    private function sendCommentNotificationToUser($comment)
    {
        $user = $comment->user;
        $notification = $user->notifications()
            ->where('type', 'App\Notifications\SubCommentNotification')
            ->where('data->user_id', $user->id)
            ->where('data->comment_id', $comment->id)
            ->first();
        if ($notification) {
            $notification->updated_at = Carbon::now();
            $notification->read_at = null;
            $notification->username = auth()->user()->full_name;
            $notification->save();
            return;
        }
        $notification = $user->notify(new SubCommentNotification([
            'username' => auth()->user()->full_name,
            'post_id' => $comment->post_id,
            'comment_id' => $comment->id
        ]));
    }
}
