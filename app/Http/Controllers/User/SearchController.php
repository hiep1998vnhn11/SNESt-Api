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
        $searchHistory = json_decode(Redis::get('search_history_user' . auth()->user()->id));
        return $this->sendRespondSuccess($searchHistory, 'Get search history successfully!');
    }

    public function search(SearchRequest $request)
    {
        $searchKey = $request->search_key;
        $searchResult = (new Search())->registerModel(User::class, 'name')->search($searchKey);
        if ($request->history) {
            $searchHistory = json_decode(Redis::get('search_history_user' . auth()->user()->id));
            if (!$searchHistory) {
                $searchHistory = [$searchKey];
                Redis::set('search_history_user' . auth()->user()->id, json_encode($searchHistory));
            } else if (array_search($searchKey, $searchHistory) !== false) {
                array_unshift($searchHistory, $searchKey);
                if (count($searchHistory) > 10) array_pop($searchHistory);
                Redis::set('search_history_user' . auth()->user()->id, json_encode($searchHistory));
            }
        }
        return $this->sendRespondSuccess($searchResult, 'Search Success!');
    }
}
