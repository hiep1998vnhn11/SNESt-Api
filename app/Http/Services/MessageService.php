<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Message;
use App\Models\Room;

class MessageService
{
    /**
     * Create a new room
     * 
     *  @param User $user_id $with_id
     *
     * @return Room
     */
    private function createRoom($user_id, $with_id)
    {
        $room = new Room();
        $room->user_id = $user_id;
        $room->with_id = $with_id;
        $room->save();
        return $room;
    }

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
    public function sendMessage($param, $room = 0)
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
}
