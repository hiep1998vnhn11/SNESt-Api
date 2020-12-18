<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Services\MessageService;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\MessageRoomRequest;
use App\Models\Room;
use App\Models\Thresh;
use Carbon\Carbon;

class MessageController extends Controller
{
    private $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->middleware('auth:api');
        $this->messageService = $messageService;
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
    public function sendMessage(MessageRequest $request)
    {
        $data = $this->messageService->sendMessage($request->all(), 1);
        if (!$data)
            return $this->sendRespondError(null, 'Send message error!', config('const.STATUS_CODE_BAD_REQUEST'));
        else return $this->sendRespondSuccess($data, 'Send message successfully!');
    }

    public function sendByRoom(MessageRoomRequest $request, Room $room)
    {
        if ($room->user_id != auth()->user()->id) return $this->sendForbidden();
        $data = $this->messageService->sendMessage($request->all(), $room);
        if (!$data)
            return $this->sendRespondError(null, 'Send message error!', config('const.STATUS_CODE_BAD_REQUEST'));
        else return $this->sendRespondSuccess($data, 'Send message successfully!');
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
    public function deleteRoom(Room $room)
    {
        if ($this->checkAuthUser($room->user_id))
            return $this->sendForbidden();
        $room->delete();
        return $this->sendRespondSuccess($room, 'Delete Room successfully!');
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
    public function delete(Message $message)
    {
        if ($this->checkAuthUser($message->user_id) || $this->checkAuthUser($message->room->user_id))
            return $this->sendForbidden();
        $message->delete();
        return $this->sendRespondSuccess($message, 'Delete Message successfully!');
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
    public function remove(Message $message)
    {
        if ($this->checkAuthUser($message->user->id) || $message->reference_id)
            return $this->sendForbidden();
        if ($message->removed)
            return $this->sendRespondError($message, 'Can not remove any time!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        $message_reference = Message::findOrFail($message->id + 1);
        if ($message_reference && $message_reference->reference_id == $message->id) {
            $message_reference->removed = 1;
            $message_reference->save();
        }
        $message->removed = 1;
        $message->save();
        return $this->sendRespondSuccess($message, 'Removed message successfully!');
    }

    /**
     * Get all message of a room!.
     *
     * @param  Room $room, 
     * @method GET
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByRoom(Room $room)
    {
        if ($this->checkAuthUser($room->user_id))
            return $this->sendForbidden();
        $data = $room->messages;
        return $this->sendRespondSuccess($data, 'Get Message by Room successfully!');
    }

    public function get(Thresh $thresh)
    {
        $isThresh = $thresh->participants()->where('user_id', auth()->user()->id)->first();
        if (!$isThresh) return $this->sendForbidden();
        $messages = $thresh->messages()->paginate(15);
        $isThresh->last_read_at = Carbon::now();
        $isThresh->save();
        return $this->sendRespondSuccess($messages, 'Get MEssage successfully!');
    }

    public function send(Thresh $thresh, MessageRoomRequest $request)
    {
        $message = new Message();
        $message->thresh_id = $thresh->id;
        $message->user_id = auth()->user()->id;
        $message->content = $request->content;
        $message->save();
        return $this->sendRespondSuccess($message, 'sendMessageSuccessfully!');
    }
}
