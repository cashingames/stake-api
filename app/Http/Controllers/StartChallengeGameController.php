<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChallengeGameSession;
use App\Models\ChallengeQuestion;
use App\Models\GameType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class StartChallengeGameController extends  BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $category = Cache::rememberForever("category_$request->category", fn () => Category::find($request->category));
        $type = Cache::rememberForever("gametype_$request->type", fn () => GameType::find($request->type));

        $challengeGameSession = new ChallengeGameSession();
        $challengeGameSession->user_id = $this->user->id;
        $challengeGameSession->game_type_id = $type->id;
        $challengeGameSession->category_id = $category->id;
        $challengeGameSession->session_token = Str::random(40);
        $challengeGameSession->start_time = Carbon::now();
        $challengeGameSession->end_time = Carbon::now()->addMinutes(1);
        $challengeGameSession->challenge_id = $request->challenge_id;
        $challengeGameSession->state = "ONGOING";


        //check for questions if the questions has been added for that challengeid


        $findQuestions = ChallengeQuestion::where('challenge_id', $request->challenge_id);

        if ($findQuestions->get()->isEmpty()) {

            $questions = [];
            $query = $category
                ->questions()
                ->where('is_published', true);

            $questions = $query->inRandomOrder()->take(20)->get()->shuffle();

            $challengeGameSession->save();

            Log::info("About to log selected game questions for game session $challengeGameSession->id and user $this->user");

            $data = [];

            foreach ($questions as $question) {
                $data[] = [
                    'question_id' => $question->id,
                    'challenge_game_session_id' => $challengeGameSession->id,
                    'challenge_id' => $request->challenge_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('challenge_questions')->insert($data);

            Log::info("questions logged for game session $challengeGameSession->id and user $this->user");
        } else {
            $qstArray = $findQuestions->pluck('question_id')->toArray();
            //$unsortIds = implode(',', $qstArray); 
            $challengeGameSession->save();
            Log::info("About to log selected game questions for game session $challengeGameSession->id and user $this->user");

            $data = [];
            foreach ($findQuestions->get() as $question) {
                $data[] = [
                    'question_id' => $question->question_id,
                    'challenge_game_session_id' => $challengeGameSession->id,
                    'challenge_id' => $request->challenge_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $questions = $category->questions()
                ->whereIn('id', $qstArray)
                ->get();
            // ->orderByRaw(DB::raw("FIELD(id, $unsortIds)"))
            DB::table('challenge_questions')->insert($data);
            Log::info("questions logged for game session $challengeGameSession->id and user $this->user");
        }

        $gameInfo = new stdClass;
        $gameInfo->token = $challengeGameSession->session_token;
        $gameInfo->startTime = $challengeGameSession->start_time;
        $gameInfo->endTime = $challengeGameSession->end_time;
        $result = [
            'questions' => $questions,
            'game' => $gameInfo
        ];
        return $this->sendResponse($result, 'Challenge Game Started');
    }
}
