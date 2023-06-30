<?php

namespace App\Services\PlayGame;

use App\Enums\GameType;
use App\Services\PlayGame\StandardExhibitionGameService;
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

        switch ($this->gameType) {
            case GameType::StandardExhibition:
                $result = new StandardExhibitionGameService();
                break;
            default:
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
