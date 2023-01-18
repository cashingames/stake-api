<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;

class ContestDetailsResponse
{
    public int $id;
    public string $startDate;
    public string $endDate;
    public string $name;
    public string $description;
    public string $displayName;
    public string $contestType;
    public string $entryMode;
    public array $prizePool;


    public function transform($data): JsonResponse
    {
        $response = [];
        foreach ($data as $contest) {
            //dd($contest->winning_prize_pools);
            $presenter = new ContestDetailsResponse;

            $presenter->id = $contest->id;
            $presenter->startDate = $contest->start_date;
            $presenter->endDate = $contest->end_date;
            $presenter->name = $contest->name;
            $presenter->displayName = $contest->display_name;
            $presenter->contestType = $contest->contest_type;
            $presenter->entryMode = $contest->entry_mode;

            //dd($contest->winning_prize_pools);
            //$presenter->prizePool = $contest->winning_prize_pools;

            $response[] = $presenter;
        }

        return response()->json($response);
    }
}
