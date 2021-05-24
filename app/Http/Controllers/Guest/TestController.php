<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Searchable\Search;
use App\Models\User;
use App\Models\Post;

class TestController extends Controller
{
    //

    public function test()
    {
        $searchResults = (new Search())
            ->registerModel(User::class, 'name', 'url')
            ->registerModel(Post::class, 'content')
            ->search('hello');
        return $this->sendRespondSuccess($searchResults, '123');
    }
}
