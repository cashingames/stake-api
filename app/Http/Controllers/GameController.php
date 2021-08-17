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
use Illuminate\Support\Facades\DB;

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
    
    public function claimAchievement($achievementId){
        $achievement = Achievement::find($achievementId);
            if($achievement===null){
                return $this->sendError('Invalid Achievement','Invalid Achievement');
            }
            $result= DB::table('user_achievements')->insert([
                'user_id' => $this->user->id,
                'achievement_id' => $achievement->id
            ]);
            return $this->sendResponse($result, 
            'Achievement Claimed');
    }

    public function startSingleGame(Request $request){
        if(!($request->has('subCatId')) ||!($request->has('gameTypeId'))||!($request->has('modeId'))){
            return $this->sendError('SubcategoryId, GametypeId and ModeId is required', 'SubcategoryId, GametypeId and ModeId is required');
        }

        $subCat = Category::find($request->subCatId);
        $gameType = GameType::find($request->gameTypeId);
        $mode = Mode::find($request->modeId);
       
        if(($subCat || $gameType || $mode) === null){
            return $this->sendResponse('Invalid subcategory, game type or mode.', 'Invalid subcategory, game type or mode.');
        }

        $easyQuestions = $subCat->questions()->where('level', 'easy')->where('game_type_id', $gameType->id)->inRandomOrder()->take(5);
        $mediumQuestions =  $subCat->questions()->where('level', 'medium')->where('game_type_id', $gameType->id)->inRandomOrder()->take(8);
        $hardQuestions = $subCat->questions()->where('level', 'hard')->where('game_type_id', $gameType->id)->inRandomOrder()->take(7);

        $questions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();

        $gameSession = new GameSession();
       
        $gameSession->user_id = $this->user->id;
        $gameSession->mode_id = $mode->id;
        $gameSession->game_type_id = $gameType->id;
        $gameSession->category_id = $subCat->id;
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

    public function startChallenge(Request $request){
        if($request->modeId == 2 && !($request->has('challengeId'))){
            return $this->sendError('Challenge Mode requires the Challenge Id', 'Challenge Mode requires the Challenge Id');
        }
        if($request->modeId == 2 && !($request->has('opponentId'))){
            return $this->sendError('Challenge Mode requires an Opponent', 'Challenge Mode requires an Opponent');
        }

        if($request->has('challengeId') && $request->has('opponentId')){
            $challenge = DB::table('challenges')->where('id', $request->challengeId)
            ->where('is_accepted',true)->first();
            
            if($challenge === null){
                return $this->sendError('Opponent is yet to accept the challenge', 
                'Opponent is yet to accept the challenge.
                You will be notified when your challenge request is accepted');
            }
            $opponent = User::find($request->opponentId); 

            if(($opponent === null )){
                return $this->sendError('Invalid Opponent', 
                'Invalid Opponent');
            }
            $subCat = Category::find($request->subCatId);
            $gameType = GameType::find($request->gameTypeId);
            $mode = Mode::find($request->modeId);

            if(($subCat || $gameType || $mode) === null){
                return $this->sendResponse('Invalid subcategory, game type or mode.', 'Invalid subcategory, game type or mode.');
            }
    
            $easyQuestions = $subCat->questions()->where('level', 'easy')->where('game_type_id', $gameType->id)->inRandomOrder()->take(5);
            $mediumQuestions =  $subCat->questions()->where('level', 'medium')->where('game_type_id', $gameType->id)->inRandomOrder()->take(8);
            $hardQuestions = $subCat->questions()->where('level', 'hard')->where('game_type_id', $gameType->id)->inRandomOrder()->take(7);
    
            $questions = $hardQuestions->union($mediumQuestions)->union($easyQuestions)->get()->shuffle();
    
            $gameSession = new GameSession();
           
            $gameSession->user_id = $this->user->id;
            $gameSession->mode_id = $mode->id;
            $gameSession->game_type_id = $gameType->id;
            $gameSession->category_id = $subCat->id;
            $gameSession->challenge_id = $request->challengeId;
            $gameSession->opponent_id = $opponent->id;
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
        return $this->sendError('This Challenge could not be started', 'This Challenge could not be started');
    }
    
    public function end(Request $request){
        $gameSession = GameSession::where("session_token", $request->sessionToken)->first();

        if ($gameSession === null){
            return $this->sendError('Game Session does not exist', 'Game Session does not exist');
        }
       //credit points to user
        $this->creditPoints($this->user->id,$request->userPointsGained,"Points gained from correct game answers");
        
        //if game is challange mode
        if($gameSession->mode_id===2){
            //credit opponent with points
            $this->creditPoints($gameSession->opponent_id,$request->opponentPointsGained,"Points gained from correct game answers");

            //save opponent details
            $gameSession->opponent_points_gained = $request->opponentPointsGained;
            $gameSession->opponent_wrong_count = $request->opponentWrongCount;
            $gameSession->opponent_correct_count = $request->opponentCorrectCount;
        }

        $gameSession->state = "COMPLETED";
        $gameSession->user_points_gained = $request->userPointsGained;   
        $gameSession->user_wrong_count= $request->userWrongCount;
        $gameSession->user_correct_count= $request->userCorrectCount;
     
        $gameSession->save();

        return $this->sendResponse($gameSession, 'Game Ended');
    }
}
