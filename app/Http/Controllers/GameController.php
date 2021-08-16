<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mode;
use App\Models\GameType;
use App\Models\Boost;
use App\Models\Category;
use App\Models\GameSession;
use App\Models\Achievement;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class GameController extends BaseController
{
    //

    public function modes(){
        return $this->sendResponse(Mode::all(), "Game Modes");
    }

    public function gameTypes(){
        return $this->sendResponse(GameType::all(), "Game Types");
    }

    public function boosts(){
        return $this->sendResponse(Boost::all(), "Game Boosts");
    }

    public function achievements(){
        return $this->sendResponse(Achievement::all(), "Achievements");
    }

    public function start($subCatId,$gameTypeId,$modeId){
        $subCat = Category::find($subCatId);
        $gameType = GameType::find($gameTypeId);
        $mode = Mode::find($modeId);
       
        if(($subCat || $gameType || $mode) === null){
            return $this->sendResponse('Invalid subcategory, game type or mode.', 'Invalid subcategory, game type or mode.');
        }

        $easyQuestions = $subCat->questions()->where('level', 'easy')->where('game_type_id', $gameTypeId)->inRandomOrder()->take(5);
        $mediumQuestions =  $subCat->questions()->where('level', 'medium')->where('game_type_id', $gameTypeId)->inRandomOrder()->take(8);
        $hardQuestions = $subCat->questions()->where('level', 'hard')->where('game_type_id', $gameTypeId)->inRandomOrder()->take(7);

        $questions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();

        $gameSession = new GameSession();

        $gameSession->user_id = $this->user->id;
        $gameSession->mode_id = $modeId;
        $gameSession->game_type_id = $gameTypeId;
        $gameSession->category_id = $subCatId;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = 'ONGOING';
        $gameSession->save();

        $result = [
            'questions' => $questions,
            'game' =>$gameSession
          ];
        return $this->sendResponse($result, 'Game Started');
    }

}
