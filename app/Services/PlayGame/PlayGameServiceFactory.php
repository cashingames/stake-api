<?php

namespace App\Services\PlayGame;

use App\Enums\GameType;
use App\Enums\ClientPlatform;
use App\Services\PlayGame\StakingExhibitionGameService;
use App\Services\PlayGame\StakingLiveTriviaGameService;
use App\Services\PlayGame\StakingChallengeGameService;
use App\Services\PlayGame\StandardExhibitionGameService;
use App\Services\PlayGame\LiveTriviaGameService;
use App\Services\PlayGame\StandardChallengeGameService;

class PlayGameServiceFactory
{

    private GameType $gameType;
    private ClientPlatform $clientPlatform;

    public function __construct(GameType $gameType, ClientPlatform $clientPlatform)
    {
        $this->gameType = $gameType;
        $this->clientPlatform = $clientPlatform;
    }

    public function getGame(): PlayGameServiceInterface
    {

        $result = null;

        switch ($this->gameType) {
            case GameType::StandardExhibition:
                $result = new StandardExhibitionGameService($this->clientPlatform);
                break;
            case GameType::StakingExhibition:
                $result = new StakingExhibitionGameService();
                break;
            case GameType::LiveTrivia:
                $result = new LiveTriviaGameService();
                break;
            case GameType::LiveTriviaStaking:
                $result = new StakingLiveTriviaGameService();
                break;
            case GameType::StandardChallenge:
                $result = new StandardChallengeGameService();
                break;
            case GameType::StakingChallenge:
                $result = new StakingChallengeGameService();
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
