<?php

namespace App\Http\Requests;

use App\Factories\GameTypeFactory;
use App\Models\LiveTrivia;
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
    }

    private function validateStartGame($validator)
    {
        $gameType = GameTypeFactory::detect($this->all());

        if (!$gameType) {
            $validator->errors()->add('type', 'Invalid game type');
            return;
        }

        switch ($gameType->name) {
            case 'StakingExhibition':
                $this->validateStakingExhibition($validator);
                break;
            case 'LiveTrivia':
                $this->validateLiveTrivia($validator);
                break;
            case 'StandardExhibition':
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