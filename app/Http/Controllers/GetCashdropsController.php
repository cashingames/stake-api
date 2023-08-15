<?php

namespace App\Http\Controllers;

use App\Actions\Cashdrop\GetCashDropsAction;

class GetCashdropsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GetCashDropsAction $getCashDropsAction)
    {
        return $getCashDropsAction->execute();
    }
}
