<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Spatie\Searchable\Search;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $searchHistory = Redis::lrange('user_search_' . auth()->user()->id, 0, -1);
        return $this->sendRespondSuccess($searchHistory, 'Get search history successfully!');
    }

    public function search(SearchRequest $request)
    {
        $searchKey = $request->search_key;
        $searchResult = (new Search())->registerModel(User::class, 'name')->search($searchKey);
        if ($request->history) {
            Redis::lpush('user_search_' . auth()->user()->id, $searchKey);
            $historyLength = Redis::llen('user_search_' . auth()->user()->id);
            if ($historyLength > 15) {
                Redis::rpop('user_search_' . auth()->user()->id);
            }
        }
        return $this->sendRespondSuccess($searchResult, 'Search Success!');
    }

    public function delete(String $value)
    {
        Redis::lrem('user_search_' . auth()->user()->id, 1, $value);
        $searchHistory = Redis::lrange('user_search_' . auth()->user()->id, 0, -1);
        return $this->sendRespondSuccess($searchHistory, 'Delete result successfully!');
    }
}
