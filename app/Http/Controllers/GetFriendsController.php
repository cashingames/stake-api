<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\FriendsDataResponse;
use App\Models\User;
use Illuminate\Http\Request;



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

        $result = $request->has('search') ? $this->searchForFriends($request->search) : $this->getFriends();

        return (new FriendsDataResponse())->transform($result);
    }

    function getFriends()
    {
        $result = User::withWhereHas(
            'profile',
            fn ($query) =>
            $query->where('referrer', $this->user->profile->referral_code)
        )->get();


        return ! $result->isEmpty() ? $result : User::with('profile:user_id,avatar')->where('id', '!=', $this->user->id)->limit(10)->get();
    }

    function searchForFriends($search)
    {
        return User::with('profile:user_id,avatar')->where('phone_number', 'like', '%' . $search . '%')
            ->orWhere('username', 'like', '%' . $search . '%')
            ->get();
    }
}
