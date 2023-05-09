<?php

namespace App\Http\Requests;

use App\Enums\GameType;
use App\Models\LiveTrivia;
use App\Models\Staking;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Facades\Cache;

class StartSinglePlayerRequest extends FormRequest
{

    public function __construct(
        private WalletRepository $walletRepository
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
        $user = auth()->user();
        $totalSessions = Staking::where('user_id', auth()->id())->count();

        if (
            $totalSessions <= config('trivia.game.demo_games_count') &&
            $user->wallet->non_withdrawable_balance <= 0
        ) {
            return $validator->errors()->add(
                'staking_amount',
                'Your demo wallet has been exhausted. Fund your wallet now to stake and earn real withdrawable cash'
            );
        } elseif (
            $totalSessions <= config('trivia.game.demo_games_count') &&
            $user->wallet->non_withdrawable_balance <  $stakingAmount
        ) {
            return $validator->errors()->add('staking_amount', 'Insufficient funds');
        } elseif (
            $totalSessions > config('trivia.game.demo_games_count') &&
            $user->wallet->non_withdrawable_balance <  $stakingAmount
        ) {
            return $validator->errors()->add('staking_amount', 'Insufficient funds');
        }

        $userProfit = $this->walletRepository->getUserProfitPercentageOnStakingToday(auth()->id());
        if ($userProfit > 300) {
            return $validator->errors()->add(
                'staking_amount',
                'You are a genius!, please try again tomorrow'
            );
        }

        $platformProfit = Cache::remember(
            'platform-profit-today',
            60 * 3,
            fn () => $this->walletRepository->getPlatformProfitPercentageOnStakingToday()
        );

        if ($platformProfit < config('trivia.platform_target') && $userProfit > 200) {
            return $validator->errors()->add(
                'staking_amount',
                'You are a genius!, please try again later'
            );
        }

        $percentWonThisYear = $this->walletRepository
            ->getUserProfitPercentageOnStakingThisYear(auth()->id());

        if ($percentWonThisYear > 300 && $platformProfit < config('trivia.platform_target')) {
            return $validator->errors()->add(
                'staking_amount',
                'You are a genius!, please try again tomorrow'
            );
        }

        //if total session is greater than 10
        $todaysSessions = Staking::where('user_id', auth()->id())
            ->whereDate('created_at', now()->toDateString())
            ->count();
        if ($todaysSessions > 10) {
            return $validator->errors()->add(
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
}
