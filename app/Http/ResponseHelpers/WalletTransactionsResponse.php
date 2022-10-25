<?php

namespace App\Http\ResponseHelpers;

use App\Enums\WalletTransactionType;
use \Illuminate\Http\JsonResponse;
use stdClass;

class WalletTransactionsResponse
{   
    public int $transactionId;
    public WalletTransactionType $type;
    public $amount;
    public string $description;
    public $transactionDate;

    public function transform($transactions): Object
    {

        $presenter = [];
        // $presenter->success = true;
        // $presenter->data = [];
        // $presenter->message = "Wallet transactions information";

        foreach ($transactions as $t) {

            $transaction = new WalletTransactionsResponse;
            $transaction->transactionId = $t->id;
            $transaction->type = $this->getTransactionType($t->type);
            $transaction->amount = $t->amount;
            $transaction->description = $t->description;
            $transaction->transactionDate = $t->transactionDate;

            $presenter[] = $transaction;
        }
        return response()->json($presenter);
    }

    private function getTransactionType($type): WalletTransactionType
    {

        if ($type == "CREDIT") {
            return WalletTransactionType::Credit;
        }
        if ($type == "DEBIT") {
            return WalletTransactionType::Debit;
        }
        return "INVALID TRANSACTION TYPE";
    }
}
