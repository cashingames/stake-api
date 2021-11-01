<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use stdClass;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'phone_number',
        'password',
        'otp_token',
        'is_on_line',
        'points',
        'user_index_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_on_line' => 'boolean',
        'points' => 'integer',
    ];

    protected $appends = [
        'achievement', 'rank', 'played_games_count',
        'challenges_played', 'win_rate'
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(WalletTransaction::class, Wallet::class);
    }

    public function points()
    {
        return $this->hasMany(UserPoint::class);
    }

    public function onlineTimelines()
    {
        return $this->hasMany(OnlineTimeline::class);
    }

    public function boosts()
    {
        return $this->hasMany(UserBoost::class);
    }

    public function achievement()
    {
        return $this->hasOne(Achievement::class);
    }

    public function quizzes()
    {
        return $this->hasMany(UserQuiz::class);
    }

    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }

    public function categories()
    {
        return $this->belongsToMany(User::class, 'category_rankings')->withPivot('points_gained', 'user_id');
    }

    public function categoryRanking()
    {
        return $this->hasOne(CategoryRanking::class);
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function getAchievementAttribute()
    {
        $latestAchievement = DB::table('user_achievements')
            ->where('user_id', $this->id)->latest()->first();

        if ($latestAchievement === null) {
            return " ";
        }
        $achievement = Achievement::where('id', $latestAchievement->achievement_id)->first();

        return ($achievement->title);
    }

    public function getRankAttribute()
    {
        $results = User::orderBy('points', 'desc')->get();

        $userIndex = -1;

        if (count($results) > 0) {
            $userIndex = $results->search(function ($user) {
                return $user->id == $this->id;
            });
        }

        if ($userIndex === false || $userIndex === -1) {
            return 786;
        }

        return $userIndex + 1;
    }

    public function getPlayedGamesCountAttribute()
    {
        $playedAsUser = GameSession::where('user_id', $this->id)->count();
        $playedAsOpponent = GameSession::where('opponent_id', $this->id)->count();

        return $playedAsUser + $playedAsOpponent;
    }
    public function getUserIndexStatusAttribute($value)
    {
        if ($value == 'CLIMBED') {
            return true;
        }
        if ($value == 'DROPPED') {
            return false;
        }
        return true;
    }

    public function getChallengesPlayedAttribute()
    {
        $playedAsUser = Challenge::where('user_id', $this->id)->count();
        $playedAsOpponent = Challenge::where('opponent_id', $this->id)->count();

        return $playedAsUser + $playedAsOpponent;
    }

    public function getWinRateAttribute()
    {
        $singleGameWins = GameSession::where('mode_id', 1)->where('user_id', $this->id)->where('user_won', true)->count();

        $challengeGameWinsAsUser = GameSession::where('mode_id', 2)->where('user_id', $this->id)->where('user_won', true)->count();

        $challengeGameWinsAsOpponent = GameSession::where('mode_id', 2)->where('opponent_id', $this->id)->where('opponent_won', true)->count();
        return (($singleGameWins + $challengeGameWinsAsUser + $challengeGameWinsAsOpponent) / 100);
    }

    public function friends()
    {

        $friends = User::where('id','!=', $this->id)->get()->map(function ($friend) {
            $data = new stdClass;
            $data->id = $friend->id;
            $data->fullName = $friend->profile->full_name;
            $data->username = $friend->username;
            $data->avatar = $friend->profile->avatar;
            return $data;
        });

        return $friends;
       
    }

}
