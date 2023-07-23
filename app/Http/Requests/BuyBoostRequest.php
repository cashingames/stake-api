<?php

namespace App\Http\Requests;

use App\Enums\WalletBalanceType;
use App\Repositories\Cashingames\BoostRepository;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class BuyBoostRequest extends FormRequest
{

    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly BoostRepository $boostRepository
    ) {
    }

    protected $stopOnFirstFailure = true;

    public function rules()
    {
        return [
            //@note: this is the boost id. Remove validation to DB to save 1 DB query
            'id' => 'bail|required',
            'wallet_type' => [
                'bail',
                'required',
                new Enum(WalletBalanceType::class)
            ],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->validateWalletBalance()) {
                    $validator->errors()->add(
                        'amount',
                        'Insufficient wallet balance to proceed'
                    );
                }
            }
        ];
    }

    /**
     * Validate the wallet balance.
     */
    protected function validateWalletBalance(): bool
    {

        $wallet = $this->walletRepository->getWalletByUserId(
            $this->user()->id
        );

        $boost = $this->boostRepository->getBoostById($this->id);

        $selectedWalletType = WalletBalanceType::from($this->wallet_type);

        switch ($selectedWalletType) {
            case WalletBalanceType::CreditsBalance:
                $walletBalance = $wallet->non_withdrawable;
                break;
            case WalletBalanceType::BonusBalance:
                $walletBalance = $wallet->bonus;
                break;
            default:
                $walletBalance = 0.0;
        }

        return $walletBalance < $boost->price;
    }
}