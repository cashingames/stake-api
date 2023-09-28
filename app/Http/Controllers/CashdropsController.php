<?php

namespace App\Http\Controllers;

use App\Repositories\Cashingames\CashdropRepository;
use Illuminate\Http\Request;

class CashdropsController extends Controller
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return [
            'cashdropRounds' => $this->cashdropRepository->getRunningCashdrops(),
            'cashdropWinners' => $this->cashdropRepository->getCashdropWinners(),
            'nextToDrop' => $this->cashdropRepository->getActiveCashdrops()->random()->cashdrop_id,
        ];
    }
}
