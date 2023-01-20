<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;


class GetContestDetailsResponse
{
    public int $id;
    public string $name;
    public string $description;
    public string $displayName;
    public string $startDate;
    public string $endDate;
    public string $contestType;
    public string $entryMode;
    public $prizePool;

    public function massTransform($contests): JsonResponse
    {

        $response = [];

        foreach ($contests as $contest) {
            $presenter = new GetContestDetailsResponse;

            $presenter->id = $contest->id;
            $presenter->name = $contest->name;
            $presenter->description = $contest->description;
            $presenter->displayName = $contest->displayName;
            $presenter->startDate = $contest->startDate;
            $presenter->endDate = $contest->endDate;
            $presenter->contestType = $contest->contestType;
            $presenter->entryMode = $contest->entryMode;
            $presenter->prizePool = $contest->winningPrizePools;

            $response[] = $presenter;
        }

        return response()->json($response);
    }

    public function singleTransform($contest): JsonResponse
    {

        $presenter = new GetContestDetailsResponse;

        $presenter->id = $contest->id;
        $presenter->name = $contest->name;
        $presenter->description = $contest->description;
        $presenter->displayName = $contest->displayName;
        $presenter->startDate = $contest->startDate;
        $presenter->endDate = $contest->endDate;
        $presenter->contestType = $contest->contestType;
        $presenter->entryMode = $contest->entryMode;
        $presenter->prizePool = $contest->winningPrizePools;


        return response()->json($presenter);
    }
    
}
