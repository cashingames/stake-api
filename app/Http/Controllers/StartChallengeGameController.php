<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
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
    public ClientPlatform $clientPlatform;

    public function __invoke(Request $request, ClientPlatform $clientPlatform)
    {
        $request->validate([
            'category' => ['required'],
            'type' => ['required'],
            'challenge_id' => ['required']
        ]);
        $this->clientPlatform = $clientPlatform;

        $category = Cache::rememberForever("category_$request->category", fn () => Category::find($request->category));
        $type = Cache::rememberForever("gametype_$request->type", fn () => GameType::find($request->type));

        $challengeGameSession = ChallengeGameSession::where([
            'user_id' => $this->user->id,
            'game_type_id' => $type->id,
            'category_id' => $category->id,
            'challenge_id' => $request->challenge_id
        ])->first();
        if ($challengeGameSession == null){
            $challengeGameSession = new ChallengeGameSession();
            $challengeGameSession->user_id = $this->user->id;
            $challengeGameSession->game_type_id = $type->id;
            $challengeGameSession->category_id = $category->id;
            $challengeGameSession->session_token = Str::random(40);
            $challengeGameSession->start_time = Carbon::now();
            $challengeGameSession->end_time = Carbon::now()->addMinutes(1);
            $challengeGameSession->challenge_id = $request->challenge_id;
            $challengeGameSession->state = "ONGOING";
            $challengeGameSession->save();
        }


        //check for questions if the questions has been added for that challengeid
        $findQuestions = ChallengeQuestion::where('challenge_id', $request->challenge_id);
        $questions = [];

        if ($findQuestions->get()->isEmpty()) {
            $questions = $this->startFirstGame($category, $request->challenge_id, $challengeGameSession->id);
        } else {
            $questions = $this->startSecondGame($category, $findQuestions, $request->challenge_id, $challengeGameSession->id);
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

    private function startFirstGame($category, $challengeId, $challengeGameSessionId)
    {
        $query = $category
            ->questions();

        $questions = $query->inRandomOrder()->take(10)->get()->shuffle();

        // check if platform is GameArk and add answers to option
        if($this->clientPlatform == ClientPlatform::GameArkMobile){
            $questions = $questions->each(function ($i, $k) {

                $i->options->each(function($ib, $kb){
                    $ib->makeVisible(['is_correct']);
                });

            });
        }
        Log::info("About to log selected game questions for game session $challengeGameSessionId and user $this->user");

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'challenge_game_session_id' => $challengeGameSessionId,
                'challenge_id' => $challengeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('challenge_questions')->insert($data);
        Log::info("questions logged for game session $challengeGameSessionId and user $this->user");
        return $questions;
    }


    private function startSecondGame($category, $findQuestions, $challengeId, $challengeGameSessionId)
    {
        $qstArray = $findQuestions->pluck('question_id')->toArray();
        //$unsortIds = implode(',', $qstArray);
        Log::info("About to log selected game questions for game session $challengeGameSessionId and user $this->user");
        $data = [];
        foreach ($findQuestions->get() as $question) {
            $data[] = [
                'question_id' => $question->question_id,
                'challenge_game_session_id' => $challengeGameSessionId,
                'challenge_id' => $challengeId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $questions = $category->questions()
            ->whereIn('question_id', $qstArray)
            ->get();

        // check if platform is GameArk and add answers to option
        if($this->clientPlatform == ClientPlatform::GameArkMobile){
            $questions = $questions->each(function ($i, $k) {

                $i->options->each(function($ib, $kb){
                    $ib->makeVisible(['is_correct']);
                });

            });
        }

        // ->orderByRaw(DB::raw("FIELD(id, $unsortIds)"))
        DB::table('challenge_questions')->insert($data);
        Log::info("questions logged for game session $challengeGameSessionId and user $this->user");
        return $questions;
    }
}
