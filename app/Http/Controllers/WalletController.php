<?php

namespace App\Http\Controllers;

class WalletController extends BaseController
{

    public function me(){
        $data = [
            'wallet' => auth()->user()->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions(){
        $data = [
            'transactions' => auth()->user()->transactions
        ];
        return $this->sendResponse($data, 'Wallet transactions information');
    }

}
