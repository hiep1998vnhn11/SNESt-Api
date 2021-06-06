<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessagePrivateRequest;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Services\MessageService;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\MessageRoomRequest;
use App\Models\Participant;
use App\Models\Room;
use App\Models\Thresh;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

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
        if ($message->user_id !== auth()->user()->id)
            return $this->sendForbidden();
        $message->delete();
        return $this->sendRespondSuccess($message, 'Delete Message successfully!');
    }

    public function reverse($message_id)
    {
        $message = Message::onlyTrashed()->findOrFail($message_id);
        if ($message->user_id !== auth()->user()->id) $this->sendForbidden();
        $message->restore();
        return $this->sendRespondSuccess($message, 'Reverse Success!');
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

    public function get(Thresh $thresh, Request $request)
    {
        $limit = Arr::get($request->all(), 'limit', config('const.DEFAULT_PER_PAGE'));
        $isThresh = $thresh->participants()->where('user_id', auth()->user()->id)->first();
        if (!$isThresh) return $this->sendForbidden();
        $messages = $thresh->messages()->withTrashed()->orderBy('created_at', 'desc')->paginate($limit);
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

    public function getCacheMessage()
    {
        $threshes = Redis::get('threshes_user' . auth()->user()->id);
        return $this->sendRespondSuccess($threshes, 'Get cache successfully!');
    }

    public function setCaceMessage(Request $request)
    {
        $threshes = Redis::set('threshes_user' . auth()->user()->id, $request->threshes);
        return $this->sendRespondSuccess($threshes, 'Set cache successfully!');
    }

    public function destroy(Message $message)
    {
        if ($message->user_id !== auth()->user()->id) return $this->sendForbidden();
        $message->delete();
        return $this->sendRespondSuccess($message, 'Deleted!');
    }

    public function privateMessage(MessagePrivateRequest $request)
    {
        if (!isset($request->content) && !$request->hasFile('file'))
            return $this->sendUnvalid();
        $thresh = Thresh::leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->where('participants.user_id', auth()->user()->id)
            ->where('threshes.type', 1)
            ->select('threshes.id', 'participants.user_id', 'threshes.type')
            ->first();
        if (!$thresh) {
            $thresh = Thresh::create([
                'type' => 1
            ]);
            Participant::create([
                'user_id' => auth()->user()->id,
                'thresh_id' => $thresh->id
            ]);
        } else {
            $thresh->updated_at = Carbon::now();
            $thresh->save();
        }
        $message = new Message();
        $message->thresh_id = $thresh->id;
        $message->user_id = auth()->user()->id;
        if ($request->content) $message->content = $request->content;
        if ($request->hasFile('file')) {
            $uploadFolder = 'public/files/messages/' . $thresh->id;
            $file = $request->file('file');
            $name = time() . '_' . $file->getClientOriginalName();
            $type = $file->getClientOriginalExtension();
            $file_path = $file->storeAs($uploadFolder, $name);
            $path = Storage::disk('local')->url($file_path);
            $message->media_type = $type;
            $message->media = config('app.url') .  $path;
            $message->media_name = $file->getClientOriginalName();
        }
        $message->save();
        return $this->sendRespondSuccess($message);
    }

    public function messageChat(MessagePrivateRequest $request, $room)
    {
        if (!isset($request->content) && !$request->hasFile('file'))
            return $this->sendUnvalid();
        $currentParticipant = Thresh::query()
            ->where('threshes.id', $room)
            ->leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->where('participants.user_id', auth()->user()->id)
            ->select('threshes.id', 'threshes.type', 'participants.user_id', 'threshes.updated_at')
            ->first();
        if (!$currentParticipant) return $this->sendForbidden();
        $message = new Message();
        $message->thresh_id = $currentParticipant->id;
        $message->user_id = auth()->user()->id;
        if ($request->content) $message->content = $request->content;
        if ($request->hasFile('file')) {
            $uploadFolder = 'public/files/messages/' . $currentParticipant->id;
            $file = $request->file('file');
            $name = time() . '_' . $file->getClientOriginalName();
            $type = $file->getClientOriginalExtension();
            $file_path = $file->storeAs($uploadFolder, $name);
            $path = Storage::disk('local')->url($file_path);
            $message->media_type = $type;
            $message->media = config('app.url') .  $path;
            $message->media_name = $file->getClientOriginalName();
        }
        $message->save();
        $currentParticipant->updated_at = Carbon::now();
        $currentParticipant->save();
        return $this->sendRespondSuccess($message);
    }

    public function privateMessageGet(Request $request, $id)
    {
        $thresh = Thresh::where('threshes.id', $id)
            ->where('threshes.type', 1)
            ->leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->select('threshes.id', 'threshes.type', 'participants.user_id')
            ->firstOrFail();
        dd($thresh);
        if ($thresh->user_id !== auth()->user()->id) return $this->sendForbidden();
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : config('const.DEFAULT_PER_PAGE');

        $messages = Message::where('thresh_id', $thresh->id)
            ->offset($offset)
            ->limit($limit)
            ->get();
        return $this->sendRespondSuccess($messages);
    }

    public function getMessageByRoom(Thresh $room, Request $request)
    {
        $params = $request->all();
        if ($room->type === 1) {
            $participant = Participant::where('thresh_id', $room->id)
                ->firstOrFail();
            if ($participant->user_id !== auth()->user()->id) return $this->sendForbidden();
            return $this->sendRespondSuccess($this->messageService->getPrivateMessage($room->id, $params));
        }
        if ($room->type === 2) {
            $currentParticipant = Participant::where('thresh_id', $room->id)
                ->where('user_id', auth()->user()->id)
                ->first();
            if (!$currentParticipant) return $this->sendForbidden();
            return $this->sendRespondSuccess($this->messageService->getMessage($room->id, $params));
        }
        if ($room->type === 3) {
            return $this->sendRespondSuccess($this->messageService->getGroupMessage($room->id, $params));
        }
        return $this->sendRespondError();
    }

    public function getType(Thresh $room)
    {
        if ($room->type === 1) {
            $participant = Participant::where('thresh_id', $room->id)
                ->firstOrFail();
            if ($participant->user_id !== auth()->user()->id) return $this->sendForbidden();
            return $this->sendRespondSuccess([
                'room' => $room,
                'participant' => auth()->user()
            ]);
        }
    }

    public function createRoom(String $url)
    {
        if ($url = auth()->user()->url) $room = $this->messageService->createPrivateRoom();
        else {
            $user = User::where('url', $url)->firstOrFail();
            $room = $this->messageService->createRoom();
        }
        return $this->sendRespondSuccess($room);
    }

    public function getRoomByUrl(String $url)
    {
        if ($url === auth()->user()->url) $room = $this->messageService->getPrivateRoom();
        else {
            $user = User::where('url', $url)
                ->select('id', 'profile_photo_path', 'url', 'full_name')
                ->firstOrFail();
            $room = $this->messageService->getRoom($user);
        }
        return $this->sendRespondSuccess([
            'room' => $room,
            'participant' => $user
        ]);
    }

    public function getRoom(Request $request)
    {
        $params = $request->all();
        $rooms = $this->messageService->getRoom($params);
        return $this->sendRespondSuccess($rooms);
    }
}
