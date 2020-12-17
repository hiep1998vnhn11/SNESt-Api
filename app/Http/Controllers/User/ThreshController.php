<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Thresh;
use Illuminate\Http\Request;

class ThreshController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'role:viewer']);
    }

    public function get()
    {
        $thresh = Thresh::find(1);
        return $thresh->users;
        return auth()->user()->threshes;
    }

    public function create()
    {
        $thresh = Thresh::create();
        $participant = new Participant();
        $participant->thresh_id = $thresh->id;
        $participant->user_id = auth()->user()->id;
        $participant->save();
        $participant->thresh;
        return $this->sendRespondSuccess($participant, 'Create participant successfully!');
    }
}
