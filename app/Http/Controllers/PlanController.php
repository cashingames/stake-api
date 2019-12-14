<?php

namespace App\Http\Controllers;

use App\Plan;
use App\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlanController extends BaseController
{
    //
    public function get()
    {
        return $this->sendResponse(Plan::all(), "General plan list");
    }

    public function subscribe(Request $request)
    {

        Validator::make($request->all(), [
            'plan_id' => ['required', 'integer'],
        ])->validate();

        $plan = Plan::find($request->plan_id);

        $user = auth()->user();
        $wallet = $user->wallet;

        if ($wallet->balance < $plan->price) {
            $errors = [
                'balance' => "Insufficient balance"
            ];
            return $this->SendError($errors, "The given data was invalid.");
        }

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $plan->price,
            'wallet_type' => 'CASH',
            'description' => 'Purchase of games lives',
            'reference' => Str::random(10)
        ]);

        $user->plans()->attach($plan->id, ['used' => 0, 'is_active' => true]);

        return $this->sendResponse(
            [
                'wallet' => $user->wallet,
                'plans' => $user->plans
            ],
            "Current user plans"
        );
    }
}
