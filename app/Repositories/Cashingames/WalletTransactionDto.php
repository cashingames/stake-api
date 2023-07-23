<?php

namespace App\Repositories\Cashingames;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use Illuminate\Support\Str;

class WalletTransactionDto
{
    public function __construct(
        public int $userId,
        public float $amount,
        public string $description,
        public WalletBalanceType $balanceType,
        public WalletTransactionType $transactionType,
        public WalletTransactionAction $action,
        // CREDIT or DEBIT,
        public ?string $reference = null,
    ) {
        $this->reference = $this->reference ?? Str::uuid();
    }

}