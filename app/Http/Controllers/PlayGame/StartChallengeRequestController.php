<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StartChallengeRequestController extends Controller
{

    public function __invoke(Request $request)
    {
        $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . auth()->user()->wallet->non_withdrawable_balance],
        ]);
        return ResponseHelper::success(Str::random(10));
    }
}