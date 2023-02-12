<?php

namespace App\Services\PlayGame;

interface PlayGameServiceInterface
{
    public function startGame(\stdClass $validatedRequest): array;
// public function saveGameSession(): void;
}