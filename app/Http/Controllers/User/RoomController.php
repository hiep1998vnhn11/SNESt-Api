<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRoomRequest;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $offset = Arr::get($params, 'offset', 0);
        $searchKey = Arr::get($params, 'search_key', null);
        $userId = auth()->user()->id;
        $conditionFirst = "";
        $conditionSecond = "";
        if ($searchKey) {
            $conditionFirst = "AND (users.full_name LIKE '%{$searchKey}%' OR users.slug LIKE '%{$searchKey}%')";
            $conditionSecond = "AND (thresh.name LIKE '%{$searchKey}%')";
        }
        $sql = "SELECT thresh.id, thresh.type, users.profile_photo_path AS photo, pt.user_id, users.url, users.full_name,
                thresh.updated_at, thresh.name
            FROM (SELECT t.type, t.id, t.updated_at, t.name
                FROM threshes t
                LEFT JOIN participants p
                    ON p.thresh_id = t.id
                WHERE p.user_id = {$userId}
                ORDER BY t.updated_at DESC
                LIMIT :limitQuery
                OFFSET :offsetQuery
            ) thresh
            LEFT JOIN participants pt
                ON pt.thresh_id = thresh.id
            LEFT JOIN users
                ON users.id = pt.user_id
            WHERE (thresh.type = 2) AND (pt.user_id <> {$userId}) {$conditionFirst}
            OR (thresh.type = 1) AND (pt.user_id = {$userId}) {$conditionFirst}
            OR (thresh.type = 3) AND (pt.user_id = {$userId}) {$conditionSecond}
        ";
        $result = DB::select($sql, [
            'limitQuery' => $limit,
            'offsetQuery' => $offset
        ]);
        return $this->sendRespondSuccess($result);
        $type = isset($request->type) ? $request->type : null;
        if ($type) $rooms = $this->getGroup($limit, $offset);
        else $rooms = $this->getRoom($limit, $offset);
        return $this->sendRespondSuccess($rooms);
    }

    private function getGroup($limit = 10, $offset = 0, $search_key = null)
    {
    }

    private function getRoom($limit = 10, $offset = 0)
    {
    }
}
