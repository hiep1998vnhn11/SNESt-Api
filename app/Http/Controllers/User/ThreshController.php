<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\ThreshService;
use App\Models\Participant;
use App\Models\Thresh;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ThreshController extends Controller
{
    private $threshService;
    public function __construct(ThreshService $threshService)
    {
        $this->middleware(['auth:api', 'role:viewer']);
        $this->threshService = $threshService;
    }

    public function get(User $user)
    {
        $threshEloquent = auth()->user()->threshes();
        if ($user->id == auth()->user()->id) { // Thresh to message to mine
            $privateThresh = $threshEloquent->where('type', 'private')->first();
            if (!$privateThresh) {
                $thresh = $this->threshService->createPrivateThresh();
                return $this->sendRespondSuccess($thresh, 'Create private thresh successfully!');
            }
            return $this->sendRespondSuccess($privateThresh, 'Get private thresh successfully!');
        } else {
            $withThresh = $threshEloquent
                ->where('type', 'with')
                ->cursor()
                ->filter(function ($thresh) use ($user) {
                    $userBelong = $thresh->participants()->where('user_id', $user->id)->first();
                    if ($userBelong) return $thresh;
                })
                ->first();
            if (!$withThresh) {
                $thresh = $this->threshService->createWithThresh($user->id);
                return $this->sendRespondSuccess($thresh, 'Create with thresh successfully!');
            } else return $this->sendRespondSuccess($withThresh, 'Get With thresh successfully!');
        }
    }

    public function create(User $user)
    {
        if ($user->id == auth()->user()->id) { // Thresh to message to mine
            $thresh = $this->threshService->createPrivateThresh();
            return $this->sendRespondSuccess($thresh, 'Create private thresh successfully!');
        } else {
            $thresh = $this->threshService->createWithThresh($user->id);
            return $this->sendRespondSuccess($thresh, 'Create with thresh successfully!');
        }
    }

    public function store(Request $request)
    {
        $limit = Arr::get($request->all(), 'limit', config('const.DEFAULT_PET_PAGE'));
        $threshes = auth()->user()->threshes()
            ->with('participants', 'represent', 'lastMessage')
            ->paginate($limit);
        return $this->sendRespondSuccess($threshes, 'Store thresh successfully!');
    }

    public function getParticipant(Thresh $thresh)
    {
        if ($thresh->type === 'private') {
            $user = auth()->user();
            return $this->sendRespondSuccess($user, 'Get private participant successfully!');
        }
        $participants = $thresh->participants();
        if ($thresh->type === 'with') {
            $participant = $participants->where('user_id', '!=', auth()->user()->id)
                ->first();
            $user = $participant->user;
            return $this->sendRespondSuccess($user, 'Get With participant successfully!');
        } else {
            $participants = $participants->get();
            return $this->sendRespondSuccess($participants, 'Get Participants successfully!');
        }
    }
}
