<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Spatie\Searchable\Search;
use Spatie\Searchable\ModelSearchAspect;

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
            Redis::zincrby('search_trending', 1, $searchKey);
        }
        return $this->sendRespondSuccess($searchResult, 'Search Success!');
    }

    public function delete(String $value)
    {
        Redis::lrem('user_search_' . auth()->user()->id, 1, $value);
        $searchHistory = Redis::lrange('user_search_' . auth()->user()->id, 0, -1);
        return $this->sendRespondSuccess($searchHistory, 'Delete result successfully!');
    }

    public function trending()
    {
        $trendingCount = Redis::zcount('search_trending', '-inf', '+inf');
        $start = $trendingCount - 10 <= 0 ? 0 : $trendingCount - 10;
        $trending = Redis::zrange('search_trending', $start, -1, 'withscores');
        return $this->sendRespondSuccess($trending, 'Get trending successfully!');
    }

    public function test()
    {
        $search = (new Search())
            ->registerModel(User::class, function (ModelSearchAspect $modelSearchAspect) {
                $modelSearchAspect
                    ->addSearchableAttribute('name') // return results for partial matches on usernames
                    ->has('posts')
                    ->with('roles');
            })
            ->search('hiep');
        return $this->sendRespondSuccess($search, '');
    }
}
