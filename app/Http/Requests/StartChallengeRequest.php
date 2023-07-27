<?php

namespace App\Http\Requests;

use App\Enums\WalletBalanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartChallengeRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'numeric'],
            'amount' => [
                'required',
                'numeric',
                "max:" . config('trivia.maximum_challenge_staking_amount'),
                "min:" . config('trivia.minimum_challenge_staking_amount')
            ],
            'wallet_type' => ['nullable', 'string']
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateStakingAmount($validator);
        });
    }

    private function validateStakingAmount($validator)
    {

        $stakingAmount = $this->input('amount');
        $user = auth()->user();

        if (strtoupper($this->input('wallet_type')) == WalletBalanceType::BonusBalance->value) {
            $this->validateBonusAccount($validator, $user, $stakingAmount);
        } else {
            $this->validateDepositAccount($validator, $user, $stakingAmount);
        }
    }

    private function validateDepositAccount($validator, $user, $stakingAmount)
    {
        app()->instance(WalletBalanceType::class, WalletBalanceType::CreditsBalance);

        if ($user->wallet->non_withdrawable < $stakingAmount) {
            $validator->errors()->add(
                'staking_amount',
                'You do not have sufficient deposit balance. Please deposit more funds.'
            );
        }
    }

    private function validateBonusAccount($validator, $user, $stakingAmount)
    {
        app()->instance(WalletBalanceType::class, WalletBalanceType::BonusBalance);
        if ($user->wallet->bonus < $stakingAmount) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient bonus balance. Please contact support for help.'
            );
            return;
        }

        $leftOverAmount = ($user->wallet->bonus - $stakingAmount);
        if ($leftOverAmount < config('trivia.minimum_challenge_staking_amount') && $leftOverAmount != 0) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient bonus amount will be left after this stake. Please stake ' . $user->wallet->bonus
            );
        }

    }
}
