<?php

namespace App\Services\PlayGame;

use App\Enums\GameType;
use App\Services\PlayGame\StakingExhibitionGameService;

class PlayGameServiceFactory
{

    private GameType $gameType;

    public function __construct(GameType $gameType)
    {
        $this->gameType = $gameType;
    }

    public function getGame(): PlayGameServiceInterface
    {

        $result = null;

        if ($this->gameType == GameType::StakingExhibition) {
            $result = app(StakingExhibitionGameService::class);
        } else {
            throw new \UnhandledMatchError("Unknown game type: " . $this->gameType);
        }

        return $result;
    }

    public function startGame(\stdClass $validatedRequest): \stdClass
    {
        $service = $this->getGame();
        return (object) $service->startGame($validatedRequest);
    }
}