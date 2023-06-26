<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Traits\Utils\DateUtils;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use DateUtils;
    use SoftDeletes;

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
        'country_code',
        'brand_id',
        'email_verified_at',
        'last_activity_time',
        'meta_data'
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
        'is_admin' => 'boolean',
    ];

    protected $appends = [
        'played_games_count','win_rate', 'full_phone_number'
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

    public function getIsAdminAttribute()
    {
        return $this->username == 'oyekunmi' || $this->username == 'zee';
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

    public function boosts()
    {
        return $this->hasMany(UserBoost::class);
    }
    public function challengeRequests()
    {
        return $this->hasMany(ChallengeRequest::class);
    }

    public function getFullPhoneNumberAttribute()
    {
        return $this->country_code . $this->phone_number;
    }

    public function scopeMostRecent($query)
    {
        return $query->orderByRaw('last_activity_time DESC');
    }

    public function categories()
    {
        return $this->belongsToMany(User::class, 'game_sessions')->withPivot('points_gained', 'user_id');
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function gameSessionQuestions()
    {
        return $this->hasManyThrough(GameSessionQuestion::class, GameSession::class);
    }

    public function bonuses()
    {
        return $this->hasManyThrough(Bonus::class, UserBonus::class);
    }
 
    public function userBonuses()
    {
        return $this->hasMany(UserBonus::class);
    }

    public function getRankAttribute()
    {
        $results = DB::select(
            "select SUM(value) as score, user_id from user_points WHERE
            point_flow_type = 'POINTS_ADDED'
            group by user_id
            order by score desc
            limit 100"
        );

        $userIndex = -1;

        if (count($results) > 0) {
            $userIndex = collect($results)->search(function ($user) {
                return $user->user_id == $this->id;
            });
        }

        if ($userIndex === false || $userIndex === -1) {
            return 786;
        }

        return $userIndex + 1;
    }

    public function getPlayedGamesCountAttribute()
    {
        return GameSession::where('user_id', $this->id)->count();
    }


    public function getAverageOfRecentGames()
    {
        $lastTwoGamesAverage = $this->gameSessions()
            ->completed()
            ->latest()
            ->limit(2)
            ->get()
            ->avg('correct_count');

        return $lastTwoGamesAverage;
    }


    public function getWinRateAttribute()
    {
        $gameWins = GameSession::where('correct_count', '>=', 5)->count();
        return ($gameWins / 100);
    }

    public function userTransactions()
    {
        return $this->transactions()
            ->select('transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('transactionDate', 'desc')
            ->limit(10)
            ->get();
    }

    public function recentGames()
    {
        return $this->gameSessions()->latest()
            ->select('category_id')
            ->groupBy('category_id')->limit(3)->get()
            ->map(function ($x) {
                return $x->category()->select('id', 'name', 'description', 'background_color as bgColor', 'icon as icon')->first();
            });
    }

    public function userAchievements()
    {
        return DB::table('user_achievements')->where('user_id', $this->id)
            ->join('achievements', function ($join) {
                $join->on('achievements.id', '=', 'user_achievements.achievement_id');
            })->select('achievements.id', 'title', 'medal as logoUrl')->get();
    }

    /**
     * @TODO eradicate this method
     * @return \Illuminate\Support\Collection
     */
    public function userBoosts()
    {
        return DB::table('user_boosts')
            ->where('user_id', $this->id)
            ->join('boosts', function ($join) {
                $join->on('boosts.id', '=', 'user_boosts.boost_id');
            })->select('boosts.id', 'boosts.point_value', 'boosts.pack_count', 'boosts.currency_value', 'boosts.icon', 'boosts.description', 'name', 'user_boosts.boost_count as count')
            ->whereNull('boosts.deleted_at')
            ->where('name', '!=', 'Bomb')
            ->where('user_boosts.boost_count', '>', 0)->get();
    }

    public function totalWithdrawals()
    {
        return $this->transactions()->withdrawals()->sum('amount');
    }

    public function notifications()
    {
        return $this->morphMany(UserNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function getUnreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    public function authTokens(){
        return $this->hasMany(AuthToken::class);
    }
}
