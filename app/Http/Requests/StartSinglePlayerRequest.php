<?php

namespace App\Http\Requests;

use App\Enums\GameType;
use App\Models\LiveTrivia;
use App\Models\Staking;
use Illuminate\Foundation\Http\FormRequest;

class StartSinglePlayerRequest extends FormRequest
{
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
            'type' => ['required', 'numeric'],
            'mode' => ['required', 'numeric'],
            'trivia' => ['nullable', 'numeric'],
            'staking_amount' => [
                'nullable',
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
            $this->validateStartGame($validator);
        });

        if ($validator->passes()) {
            app()->instance(GameType::class, GameType::detect($this->all()));
        }
    }

    private function validateStartGame($validator)
    {
        $gameType = GameType::detect($this->all());

        if (!$gameType) {
            $validator->errors()->add('type', 'Invalid game type');
            return;
        }

        switch ($gameType) {
            case GameType::StakingExhibition:
                $this->validateStakingExhibition($validator);
                break;
            case GameType::LiveTrivia:
                $this->validateLiveTrivia($validator);
                break;
            case GameType::StandardExhibition:
                $this->validateStandardExhibition($validator);
                break;
            default:
                $validator->errors()->add('type', 'Invalid game type');
                break;
        }

    }

    private function validateStakingExhibition($validator)
    {
        $stakingAmount = $this->input('staking_amount');

        if (auth()->user()->wallet->non_withdrawable_balance < $stakingAmount) {
            $validator->errors()->add('staking_amount', 'Insufficient funds');
        }

        $userProfit = $this->getUserProfitToday(auth()->user());
        if ($userProfit > 300) {
            $validator->errors()->add(
                'staking_amount',
                'You are a genius!, please try again tomorrow'
            );
        }

        $platformProfit = $this->getPlatformProfitToday();
        if ($platformProfit < 30 && $userProfit > 100) {
            $validator->errors()->add(
                'staking_amount',
                'You are a genius!, please try again later'
            );
        }

        //if total session is greater than 10
        $totalSession = Staking::where('user_id', auth()->id())
            ->whereDate('created_at', now()->toDateString())
            ->count();
        if ($totalSession > 10) {
            $validator->errors()->add(
                'staking_amount',
                'You have reached your daily limit of 10 games, please try again tomorrow'
            );
        }

    }

    private function validateLiveTrivia($validator)
    {

        $trivia = LiveTrivia::find($this->input('trivia'));
        if (!$trivia) {
            $validator->errors()->add('trivia', 'Unknown trivia');
            return;
        }

        //@TODO cover these edge cases

        // if ($trivia->status != 'active') {
        //     $validator->errors()->add('trivia', 'Trivia is not active');
        // }

        // if ($trivia->start_time > now()) {
        //     $validator->errors()->add('trivia', 'Trivia has not started');
        // }

        // if ($trivia->end_time < now()) {
        //     $validator->errors()->add('trivia', 'Trivia has ended');
        // }

        if (auth()->user()->gameSessions()->where('trivia_id', $trivia->id)->exists()) {
            $validator->errors()->add('trivia', 'You have already played this trivia');
        }


    }

    private function validateStandardExhibition($validator)
    {
        $plan = auth()->user()->getNextFreePlan() ?? auth()->user()->getNextPaidPlan();
        if ($plan == null) {
            $validator->errors()->add('type', 'You do not have a valid plan');
        }
    }

    /**
     * To calculate the percentage profit, you need to calculate the difference between the amount received
     * and the initial stake, and then divide by the initial stake and multiply by 100.
     * e.g I staked with 100 and got 15 back how much did I profit in percentage
     * In this case, the amount received was 15 and the initial stake was 100. So the profit would be:
     * (15 – 100) / 100 = -85%
     * Note that the result is negative, which means that there was a loss rather than a profit.
     *
     * If the amount received was greater than the initial stake, the result would be positive.
     * e.g I staked with 100 and got 150 back how much did I profit in percentage
     * In this case, the amount received was 150 and the initial stake was 100. So the profit would be:
     * (150 – 100) / 100 = 50%
     * Note that the result is positive, which means that there was a profit rather than a loss.
     *
     * @param mixed $user
     * @return float
     */

    private function getUserProfitToday($user): float
    {
        $todayStakes = Staking::whereDate('created_at', '=', date('Y-m-d'))
            ->where('user_id', $user->id)
            ->selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;

        if ($amountStaked == 0) {
            return 0;
        }

        if ($amountWon == 0) {
            return -100;
        }

        return (($amountWon - $amountStaked) / $amountStaked) * 100;
    }

    /**
     * Platform profit is the opposite of total users profit
     * e,g if users profit is 10%, then platform profit is -10%
     *
     * @return float|int
     */
    private function getPlatformProfitToday()
    {
        $todayStakes = Staking::whereDate('created_at', '=', date('Y-m-d'))
            ->selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;


        /**
         * If no stakes were made today, then the platform is neutral
         * So first user should be lucky
         */
        if ($amountWon == 0) {
            return 100;
        }

        if ($amountStaked == 0) {
            return 0;
        }

        return (($amountWon - $amountStaked) / $amountStaked) * 100;
    }


}
