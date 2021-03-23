<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Validator;

class PlanController extends BaseController
{
    //
    public function get()
    {
        return $this->sendResponse(Plan::where('is_free',0)->get(), "Non-free plan list");
    }

    public function subscribe(Request $request)
    {
        Validator::make($request->all(), [
            'plan_id' => ['required', 'integer'],
        ])->validate();

        $plan = Plan::find($request->plan_id);

        $wallet = $this->user->wallet;

        if ($wallet->balance < $plan->price) {
            $errors = [
                'balance' => "Insufficient balance"
            ];
            return $this->sendError($errors, "The given data was invalid.");
        }

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $plan->price,
            'wallet_kind' => 'CREDITS',
            'description' => 'PURCHASE OF GAME LIVES',
            'reference' => Str::random(10)
        ]);

        $this->user->plans()->attach($plan->id, ['used' => 0, 'is_active' => true]);

        $this->user->wallet->refresh();
        return $this->sendResponse(
            [
                'wallet' => $this->user->wallet,
                'plans' => $this->user->plans()->wherePivot('is_active', true)->get(),
            ],
            "Current user plans"
        );
    }
}
