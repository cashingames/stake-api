<?php

namespace App\Http\ResponseHelpers;

use App\Enums\WalletTransactionType;
use Carbon\Carbon;
use \Illuminate\Http\JsonResponse;
use stdClass;
use App\Traits\Utils\DateUtils;

class WalletTransactionsResponse
{
    use DateUtils;
    public int $transactionId;
    public WalletTransactionType $type;
    public $amount;
    public string $description;
    public $transactionDate;

    public function transform($transactions): Object
    {

        $presenter = [
            "all" => $this->transformAllTransactions($transactions),
            "credits" => $this->transformCreditTransactions($transactions),
            "debits" => $this->transformDebitTransactions($transactions)
        ];


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

    private function transformAllTransactions($transactions)
    {
        $data = [];

        foreach ($transactions as $t) {

            $transaction = new WalletTransactionsResponse;
            $transaction->transactionId = $t->id;
            $transaction->type = $this->getTransactionType($t->type);
            $transaction->amount = $t->amount;
            $transaction->description = $t->description;
            $transaction->transactionDate = $this->toNigeriaTimeZoneFromUtc($t->transactionDate)->toDateTimeString();

            $data[] = $transaction;
        }
        return $data;
    }

    private function transformCreditTransactions($transactions)
    {
        $data = [];

        foreach ($transactions as $t) {
            if ($t->type ==  WalletTransactionType::Credit->value) {
                $transaction = new WalletTransactionsResponse;
                $transaction->transactionId = $t->id;
                $transaction->type = $this->getTransactionType($t->type);
                $transaction->amount = $t->amount;
                $transaction->description = $t->description;
                $transaction->transactionDate = $this->toNigeriaTimeZoneFromUtc($t->transactionDate)->toDateTimeString();

                $data[] = $transaction;
            }
        }
        return $data;
    }

    private function transformDebitTransactions($transactions)
    {
        $data = [];

        foreach ($transactions as $t) {
            if ($t->type ==  WalletTransactionType::Debit->value) {
                $transaction = new WalletTransactionsResponse;
                $transaction->transactionId = $t->id;
                $transaction->type = $this->getTransactionType($t->type);
                $transaction->amount = $t->amount;
                $transaction->description = $t->description;
                $transaction->transactionDate = $this->toNigeriaTimeZoneFromUtc($t->transactionDate)->toDateTimeString();

                $data[] = $transaction;
            }
        }
        return $data;
    }
}
