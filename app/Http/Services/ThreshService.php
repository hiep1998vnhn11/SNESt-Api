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
use Aws\Endpoint\Partition;

class ThreshService
{
  /**
   * Create a new room
   * 
   *  @param User $user_id $with_id
   *
   * @return Room
   */
  public function createPrivateThresh()
  {
    $thresh = new Thresh();
    $thresh->type = 'private';
    $thresh->save();
    $participant = new Participant();
    $participant->user_id = auth()->user()->id;
    $participant->thresh_id = $thresh->id;
    $participant->save();
    return $thresh;
  }

  public function createWithThresh($userId)
  {
    $thresh = new Thresh();
    $thresh->type = 'with';
    $thresh->save();
    $participant = new Participant();
    $participant->user_id = auth()->user()->id;
    $participant->thresh_id = $thresh->id;
    $participant->save();
    $withParticipant = new Participant();
    $withParticipant->user_id = $userId;
    $withParticipant->thresh_id = $thresh->id;
    $withParticipant->save();
    return $thresh;
  }
}
