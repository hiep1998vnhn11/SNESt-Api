<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $searchHistory = Redis::get('search_history_user' . auth()->user()->id);
        if (count($searchHistory) === 10) {
            return 1;
        }
    }
}
