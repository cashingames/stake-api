<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\FriendsDataResponse;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class GetFriendsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->has('search')) {
            $referralcode = Auth::user()->profile->referral_code;
            $getProfiles =  Profile::where('referrer', $referralcode)->get();
            if ($getProfiles->count() > 0) {
                $result = User::withWhereHas(
                    'profile',
                    fn ($query) =>
                    $query->where('referrer', $referralcode)
                )->get();
                return (new FriendsDataResponse())->transform($result);
            } else {

                $result = User::with('profile')->where('id', '!=', Auth::user()->id)->limit(10)->get();
                return (new FriendsDataResponse())->transform($result);
            }
        }
        $search = $request->search;
        $result = User::with('profile')->where('phone_number', 'like', '%' . $search . '%')
            ->orWhere('username', 'like', '%' . $search . '%')
            ->get();
        return (new FriendsDataResponse())->transform($result);
    }
}
