<?php

namespace App\Http\Requests;

use App\Enums\WalletBalanceType;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;

class StartSinglePlayerRequest extends FormRequest
{

    public function __construct(
        private WalletRepository $walletRepository,
        private RegistrationBonusService $registrationBonusService
    ) {
    }
    /**
     * Summary of walletRepository
     * @var
     */

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'category' => ['required', 'numeric'],
            'staking_amount' => [
                'required',
                'numeric',
                "max:" . config('trivia.maximum_exhibition_staking_amount'),
                "min:" . config('trivia.minimum_exhibition_staking_amount')
            ],
            'wallet_type' => ['nullable', 'string']
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) {
            return;
        }

        $validator->after(function ($validator) {
            $this->validateStakingAmount($validator);
        });
    }

    private function validateStakingAmount($validator)
    {

        $stakingAmount = $this->input('staking_amount');
        $user = auth()->user();

        if ($this->input('wallet_type') == 'bonus_balance') {
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
        if ($leftOverAmount < config('trivia.minimum_exhibition_staking_amount') && $leftOverAmount != 0) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient bonus amount will be left after this stake. Please stake ' . $user->wallet->bonus
            );
            return;
        }

    }

}