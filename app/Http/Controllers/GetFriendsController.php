<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\FriendsDataResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetFriendsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function __invoke(Request $request)
    {

        $result = $request->has('search') ? $this->searchForFriends($request->search) : $this->getOnlineUsers();

        return (new FriendsDataResponse())->transform($result);
    }

    function getOnlineUsers()
    {
        return User::mostRecent()->with('profile:user_id,avatar')->where('id', '!=', $this->user->id)->paginate(20);
    }

    function searchForFriends($search)
    {
        return User::mostRecent()->with('profile:user_id,avatar')->where('phone_number', 'like', '%' . $search . '%')
            ->orWhere('username', 'like', '%' . $search . '%')
            ->paginate(20);
    }
}
