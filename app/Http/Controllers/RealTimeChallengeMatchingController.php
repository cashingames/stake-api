<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RealTimeChallengeMatchingController extends BaseController
{
    public function __invoke(Request $request){

        $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $this->user->wallet->non_withdrawable_balance],
        ]);
        
    }
}