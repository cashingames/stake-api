<?php

namespace App\Http\Requests;

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
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric'],
        ];
    }

    public function withValidator(Validator $validator): void
    {   
        
        $validator->after(function (Validator $validator) {
            if ($this->input('amount') > auth()->user()->wallet->non_withdrawable) {
                $validator->errors()->add('amount', 'Insufficient Balance');
            }
            if ($this->input('amount') > config('trivia.maximum_challenge_staking_amount')) {
                $validator->errors()->add('amount', 'Amount should not be more than ' . config('trivia.maximum_challenge_staking_amount'));
            }
            if ($this->input('amount') < config('trivia.minimum_challenge_staking_amount')) {
                $validator->errors()->add('amount', 'Amount should not be less than ' . config('trivia.minimum_challenge_staking_amount'));
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Insufficient Balance',
        ];
    }
}
