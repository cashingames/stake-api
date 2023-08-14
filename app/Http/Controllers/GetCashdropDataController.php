<?php

namespace App\Http\Controllers;

use App\Actions\Cashdrop\GetCashDropDataAction;
use Illuminate\Http\Request;

class GetCashdropDataController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GetCashDropDataAction $getCashDropDataAction)
    {
        return $getCashDropDataAction->execute();
    }
}
