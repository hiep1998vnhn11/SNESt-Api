<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Get all user with $request->search_key
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUserForIdentify(Request $request)
    {
        $searchKey = $request->search_key;
        $user = User::select(['name', 'profile_photo_path', 'email'])
            ->where('email', $searchKey)
            ->first();
        return $this->sendRespondSuccess($user, 'Search user for identify successfully!');
    }
}
