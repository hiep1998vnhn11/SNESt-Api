<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Room;
use App\Models\Thresh;
use Illuminate\Support\Facades\DB;

class MessageService
{
    /**
     * Create a new message
     * 
     * @param Room $room_id
     * @param User $user_id
     * @param String $content
     *
     * @return Message
     */
    private function createMessage($room_id, $user_id, $content, $reference_id = null)
    {
        $message = new Message();
        $message->content = $content;
        $message->room_id = $room_id;
        $message->user_id = $user_id;
        if ($reference_id)
            $message->reference_id = $reference_id;
        $message->save();
        return $message;
    }

    /**
     * update a message
     * 
     * @param Message $message
     * @param String $content
     *
     * @return Message
     */
    private function updateMessage($message, $content)
    {
        $message->content = $content;
        $message->save();
        return $message;
    }

    /**
     * Main function send an message
     * 
     * @param Request->all $param
     *
     * @return Message
     */
    public function sendMessage($param, $room)
    {
        if (!$room) {
            $user_url = Arr::get($param, 'user_url', null);
            $content = Arr::get($param, 'content', null);
            if ($user_url == auth()->user()->url)
                return $this->sendMessageToMine(auth()->user(), $content);
            $user = User::select('id', 'name')
                ->where('url', $user_url)
                ->first();
            if (!$user) return false;
            else {
                $isFriend = $user->friends->where('friend_id', auth()->user()->id)->first();
                if ($isFriend && $isFriend->blocked) return false;
                return $this->sendMessageToWith(auth()->user(), $user, $content);
            }
        } else {
            $content = Arr::get($param, 'content', null);
            return $this->createMessage($room->id, auth()->user()->id, $content);
        }
    }


    /**
     * Create a new message to mine(current user)
     * 
     * @param User $user_id
     * @param String $content
     *
     * @return Message
     */
    private function sendMessageToMine($user, $content)
    {
        $room = $user->rooms()->where('with_id', $user->id)->first();
        if (!$room) $room = $this->createRoom($user->id, $user->id);
        return $this->createMessage($room->id, $user->id, $content);
    }

    /**
     * Create a new message to $user_with
     * 
     * @param User $user
     * @param User $user_with
     * @param String $content
     *
     * @return Message
     */
    private function sendMessageToWith($user, $user_with, $content)
    {
        $room = $user->rooms->where('with_id', $user_with->id)->first();
        if (!$room) $room = $this->createRoom($user->id, $user_with->id);
        $room_with = $user_with->rooms->where('with_id', $user->id)->first();
        if (!$room_with) $room_with = $this->createRoom($user_with->id, $user->id);

        $message = $this->createMessage($room->id, $user->id, $content);
        $this->createMessage($room_with->id, $user->id, $content, $message->id);
        return $message;
    }

    public function getPrivateMessage($roomId, $params)
    {
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $offset = Arr::get($params, 'offset', 0);
        $messages = Message::where('thresh_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        return $messages;
    }

    public function getMessage($roomId, $params)
    {
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $offset = Arr::get($params, 'offset', 0);
        $messages = Message::where('thresh_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        return $messages;
    }

    public function getPrivateRoom()
    {
        $room = Thresh::where('threshes.type', 1)
            ->leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->where('participants.user_id', auth()->user()->id)
            ->select('threshes.id', 'threshes.type', 'participants.user_id')
            ->first();
        if (!$room) {
            $room = Thresh::create([
                'type' => 1
            ]);
            Participant::create([
                'user_id' => auth()->user()->id,
                'thresh_id' => $room->id
            ]);
        }
        return $room;
    }

    public function getRoom($user)
    {
        $sql = "SELECT t.id, t.type, count(p.user_id) AS participants_count
            FROM `threshes` t
            LEFT JOIN `participants` p
                ON p.thresh_id = t.id
            WHERE p.user_id IN (:user_first, :user_second)
            GROUP BY t.id, t.type
            HAVING count(p.user_id) = 2
            LIMIT 1
        ";
        $result = DB::select($sql, [
            'user_first' => auth()->user()->id,
            'user_second' => $user->id
        ]);
        if ($result) return $result[0];
        $room = Room::create([
            'type' => 1
        ]);
        Thresh::insert([
            [
                'user_id' => auth()->user()->id,
                'thresh_id' => $room->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'user_id' => auth()->user()->id,
                'thresh_id' => $room->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        $room->participants_count = 2;
        return $room;
    }
}
