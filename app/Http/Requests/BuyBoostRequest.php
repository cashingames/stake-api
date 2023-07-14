<?php

namespace App\Http\Requests;

use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\Boost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class BuyBoostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'boost_id' => ['required', 'numeric'],
            'wallet_type' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
       
        $validator->after(function (Validator $validator) {

            $boost = Boost::find($this->input('boost_id'));
          
            if(is_null($boost)){
                return $validator->errors()->add('boost_id', 'Boost not found');
            }

            $user = auth()->user();
            $wallet = $user->wallet;

            if ($this->input('wallet_type') == 'bonus_balance') {
                if ($wallet->bonus < ($boost->currency_value)) {
                    return $validator->errors()->add('wallet_type','You do not have enough money in your bonus wallet.');
                }
            } else {
                if ($wallet->non_withdrawable < ($boost->currency_value)) {
                    return $validator->errors()->add('wallet_type','You do not have enough money in your deposit wallet.');
                }
            }
        });
    }

    public function prepareForValidation()
    {
        $this->merge([
            'boost_id' => $this->route('boostId'),
        ]);
    }
    
}
