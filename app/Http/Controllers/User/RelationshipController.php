<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Http\Request;

class RelationshipController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store()
    {
        $relationships = auth()->user()->requester()->where('status', 2)->get();
        return $this->sendRespondSuccess($relationships, 'Store relationships');
    }

    public function addFriend(User $user)
    {
        // Nếu đã là bạn thì không thể adđ friend
        $checkRelationship = auth()->user()->relationships()
            ->where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id)
            ->first();
        if ($checkRelationship) {
            switch ($checkRelationship->status) {
                case 0: //Accept friend
                    if ($checkRelationship->requester_id == auth()->user()->id)
                        return $this->sendForbidden();
                    else {
                        $checkRelationship->status = 1;
                        $checkRelationship->action_id = auth()->user()->id;
                        $checkRelationship->save();
                        break;
                    }
                case 1:
                    return $this->sendRespondError($checkRelationship, 'Had been friend!');
                case 3:
                    return $this->sendRespondSuccess($checkRelationship, 'Blocked!');
                default: //2 => đang huỷ kết bạn
                    $checkRelationship->status = 1;
                    $checkRelationship->requester_id = auth()->user()->id;
                    $checkRelationship->addressee_id = $user->id;
                    $checkRelationship->action_id = auth()->user()->id;
                    $checkRelationship->save();
                    break;
            }
        } else {
            $relationship = new Relationship();
            $relationship->requester_id = auth()->user()->id;
            $relationship->addressee_id = $user->id;
            $relationship->requester_id = auth()->user()->id;
            $relationship->status = 1;
        }
        return $this->sendRespondSuccess($checkRelationship ?? $relationship, 'add friend success!');
    }
    public function block(User $user)
    {
        // Nếu đã là bạn thì không thể adđ friend
        $checkRelationship = auth()->user()->relationships()
            ->where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id)
            ->first();
        if ($checkRelationship) {
            $checkRelationship->status = 3;
            $checkRelationship->action_id = auth()->user()->id;
            $checkRelationship->save();
        } else {
            $relationship = new Relationship();
            $relationship->requester_id = auth()->user()->id;
            $relationship->addressee_id = $user->id;
            $relationship->requester_id = auth()->user()->id;
            $relationship->status = 3;
        }
        return $this->sendRespondSuccess($checkRelationship ?? $relationship, 'add friend success!');
    }
    public function unBlock(User $user)
    {
        // Nếu đã là bạn thì không thể adđ friend
        $checkRelationship = auth()->user()->relationships()
            ->where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id)
            ->first();
        if (!$checkRelationship) {
            return $this->sendRespondError(null, 'Not have an relationship');
        } else if ($checkRelationship->status == 3) {
            $checkRelationship->status = 2;
            $checkRelationship->action_id = auth()->user()->id;
            $checkRelationship->save();
        } else return $this->sendRespondError($checkRelationship, 'not blocked!', 500);
    }
    public function unFriend(User $user)
    {
        // Nếu đã là bạn thì không thể adđ friend
        $checkRelationship = auth()->user()->relationships()
            ->where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id)
            ->first();
        if ($checkRelationship) {
            switch ($checkRelationship->status) {
                case 0: //Accept friend
                case 1:
                    $checkRelationship->status = 2;
                    $checkRelationship->action_id = auth()->user()->id;
                    $checkRelationship->save();
                    break;
                case 3:
                    return $this->sendRespondError($checkRelationship, 'Blocked!', 500);
                default: //2 => đang huỷ kết bạn
                    return $this->sendRespondError($checkRelationship, 'not friend yet!');
            }
        } else return $this->sendRespondError(null, 'not be friend!');
    }

    public function get()
    {
        $data = auth()->user()->relationships()->where('requester_id', 4)->orWhere('addressee_id', 4)->first();
        return $this->sendRespondSuccess($data, 'as');
    }

    public function friend(User $user)
    {
        if ($user->id === auth()->id) return $this->sendForbidden();
    }
}
