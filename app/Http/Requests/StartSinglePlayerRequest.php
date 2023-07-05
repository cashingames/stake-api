<?php

namespace App\Http\Requests;

use App\Enums\GameType;
use App\Enums\StakingFundSource;
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
            ]
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
            $this->validate($validator);
        });

        if ($validator->passes()) {
            app()->instance(GameType::class, GameType::StakingExhibition);
        }
    }

    private function validate($validator)
    {
        $this->validateStakingAmount($validator);
    }

    private function validateStakingAmount($validator)
    {

        $stakingAmount = $this->input('staking_amount');
        $user = auth()->user();

        if ($user->wallet->hasBonus()) {
            $this->validateBonusAccount($validator, $user, $stakingAmount);
        } else {
            $this->validateDepositAccount($validator, $user, $stakingAmount);
        }
    }

    private function validateDepositAccount($validator, $user, $stakingAmount)
    {
        app()->instance(StakingFundSource::class, StakingFundSource::DEPOSIT);

        if ($user->wallet->non_withdrawable < $stakingAmount) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient Deposit'
            );

        }
    }

    private function validateBonusAccount($validator, $user, $stakingAmount)
    {
        app()->instance(StakingFundSource::class, StakingFundSource::BONUS);

        if ($user->wallet->bonus < $stakingAmount) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient bonus balance.'
            );
            return;
        }
        
        if (($user->wallet->bonus - $stakingAmount) < config('trivia.minimum_exhibition_staking_amount')) {
            $validator->errors()->add(
                'staking_amount',
                'Insufficient bonus amount will be left after this stake. Please stake ' . $user->wallet->bonus
            );
            return;
        }

        $registrationBonus = $this->registrationBonusService->activeRegistrationBonus($user);
        if ($registrationBonus != null) {
            $this->validateRegistrationBonus($validator, $user, $registrationBonus, $stakingAmount);
        }

    }

    private function validateRegistrationBonus($validator, $user, $bonus, $stakingAmount)
    {   
        if ($stakingAmount > $bonus->amount_remaining_after_staking) {
            $validator->errors()->add(
                'staking_amount',
                'Registration bonus is remaining ' .
                $bonus->amount_remaining_after_staking .
                ' please stake ' . $bonus->amount_remaining_after_staking
            );
        } elseif ($this->registrationBonusService->hasPlayedCategory($user, $this->input('category'))) {
            $validator->errors()->add(
                'category',
                'Sorry, you cannot play a category twice using your welcome bonus, Please play another category.'
            );
        }
    }

}