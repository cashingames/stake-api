<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'user_id', 'opponent_id', 'status'];

    public function users()
    {

        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id', 'id');
    }



    public function challengeGameSessions()
    {
        return $this->hasMany(ChallengeGameSession::class);
    }

    public static function changeChallengeStatus($status, $id)
    {

        $challenge = Challenge::find($id);
        $challenge->update(['status' =>  $status]);

        return $challenge;
    }

    public static function challengeDetails(int $id)
    {   
        $challenge = Challenge::find($id);
        if($challenge !== null){
            $gameMode = GameMode::where('name', 'CHALLENGE')->first();

            $player = User::find($challenge->user_id);
            $playerUsername = $player->username;
            $playerAvater = $player->profile->avatar;
    
            $opponent = User::find($challenge->opponent_id);
            $opponentUsername = $opponent->username;
            $opponentAvatar = $opponent->profile->avatar;
    
            $data = new stdClass;
            $data->challengeDetails = $challenge;
            $data->gameModeId = $gameMode->id;
            $data->gameModeName = $gameMode->name;
            $data->playerUsername =  $playerUsername;
            $data->playerAvatar = $playerAvater;
            $data->opponentUsername = $opponentUsername;
            $data->opponentAvatar = $opponentAvatar;
            return $data;
        }
    }

    public function stakings(){
        return $this->hasMany(ChallengeStaking::class, 'challenge_id');
    }
}
