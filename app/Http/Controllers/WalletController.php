<?php

namespace App\Http\Controllers;

use App\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    //

    public function me(){
        return auth()->user()->wallet;
    }
    public function transactions(){
        return auth()->user()->transactions;
    }
}
