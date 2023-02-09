<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;
use stdClass;

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
            $presenter = $this->makePresenter($contest);
            $response[] = $presenter;
        }

        return response()->json($response);
    }

    public function singleTransform($contest): JsonResponse
    {
        $presenter = $this->makePresenter($contest);
        return response()->json($presenter);
    }

    private function makePresenter($contest)
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
        $presenter->prizePool = $this->transformPrizePools($contest->contestPrizePools ?? []);

        return $presenter;
    }

    public function transformPrizePools($prizePools)
    {
        $data = [];

        foreach ($prizePools as $_prizePool) {
         
            $data[] =  $this->transformPrizePool($_prizePool);
        }
        return $data;
    }

    public function transformPrizePool($_prizePool)
    {
        $_presenter =  new stdClass;
        $_presenter->id = $_prizePool->id;
        $_presenter->contestId = $_prizePool->contest_id;
        $_presenter->rankFrom = $_prizePool->rank_from;
        $_presenter->rankTo = $_prizePool->rank_to;
        $_presenter->prize = $_prizePool->prize;
        $_presenter->prizeType = $_prizePool->prize_type;
        $_presenter->eachPrize = $_prizePool->each_prize;
        $_presenter->netPrize = $_prizePool->net_prize;

        return $_presenter;
    }
}
