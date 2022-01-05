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
use Illuminate\Support\Carbon;

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
    ];

    protected $appends = [
        'achievement', 'rank', 'played_games_count',
        'challenges_played', 'win_rate', 'active_plans'
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

    public function userPlan()
    {
        return $this->hasMany(UserPlan::class);
    }

    public function boosts()
    {
        return $this->hasMany(UserBoost::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function plan()
    {
        return $this->hasMany(Plan::class);
    }

    public function categories()
    {
        return $this->belongsToMany(User::class, 'game_sessions')->withPivot('points_gained', 'user_id');
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function points()
    {
        $pointsAdded = UserPoint::where('user_id', $this->id)
            ->where('point_flow_type', 'POINTS_ADDED')
            ->sum('value');
        $pointsSubtracted = UserPoint::where('user_id', $this->id)
            ->where('point_flow_type', 'POINTS_SUBTRACTED')
            ->sum('value');
        return $pointsAdded -  $pointsSubtracted;
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

    public function hasActivePlan()
    {            
        //Check if it's a new day
        if(Carbon::now()->isAfter(Carbon::today()->startOfDay())){
            //get active free plan
            $freePlan = $this->userPlan->where('plan_id', 1)->where('is_active', true)->first();
            if($freePlan === null){
                //check last given free plan
                $lastFreePlan = UserPlan::where('user_id', $this->id)->where('plan_id', 1)->latest()->first();
                //check if it's yesterday's own
                if($lastFreePlan->updated_at->between(Carbon::yesterday(),Carbon::today()->startOfDay())){
                //give free plan for today
                    UserPlan::create([
                        'plan_id' => 1,
                        'user_id' => $this->id,
                        'used_count' => 0,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    //check if user has any other active plan
                    $otherActivePlan = $this->userPlan->where('is_active', true)->first();
                    if($otherActivePlan === null){
                        return true;
                    }
                    if($otherActivePlan->used_count >= Plan::find($otherActivePlan->plan_id)->game_count){
                        //deactivate plan
                        $otherActivePlan->update(['is_active'=>false]);
                        return true;
                    }
                    //user has existing paid plan so,
                    return true;
                }
                //if not yesterday's own, check other plans
                $otherActivePlan = $this->userPlan->where('is_active', true)->first();
                if($otherActivePlan === null){
                    return false;
                }
                if($otherActivePlan->used_count >= Plan::find($otherActivePlan->plan_id)->game_count){
                    //deactivate plan
                    $otherActivePlan->update(['is_active'=>false]);
                    return false;
                }
                return true;
            }
           //check if it's that of previous day
          
            if($freePlan->updated_at->between(Carbon::yesterday(),Carbon::today()->startOfDay())){
                //deactivate free plan
                $freePlan->update(['is_active'=>false]);
                //insert new free plan for the day
                UserPlan::create([
                    'plan_id' => 1,
                    'user_id' => $this->id,
                    'used_count' => 0,
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                //check if user has any other active plan
                $otherActivePlan = $this->userPlan->where('is_active', true)->first();
                if($otherActivePlan === null){
                    return true;
                }
                if($otherActivePlan->used_count >= Plan::find($otherActivePlan->plan_id)->game_count){
                    //deactivate plan
                    $otherActivePlan->update(['is_active'=>false]);
                    return true;
                }
                return true;
            }  
            //if not that of previous day, check the number of game counts for the active free
            if($freePlan->used_count >= Plan::find(1)->game_count){
                //deactivate free plan
                $freePlan->update(['is_active'=>false]);

                //check other plan count
                $otherActivePlan = $this->userPlan->where('is_active', true)->first();
                if($otherActivePlan === null){
                    return false;
                }
                if($otherActivePlan->used_count >= Plan::find($otherActivePlan->plan_id)->game_count){
                    //deactivate plan
                    $otherActivePlan->update(['is_active'=>false]);
                    return false;
                }
                return true;
            }
            return true;
        }
        return false;
    }

    public function getActivePlansAttribute()
    {   
        $subscribedPlan = UserPlan::where('user_id', $this->id)
                                    ->where('is_active', true)
                                    ->get();
        if(count($subscribedPlan) === 0){
          return [];
        }
        
        $subscribedPlans = [];
        $purchasedPlan =  new stdClass;
        $purchasedPlan->name = "Purchased Games";
        $purchasedPlan->background_color = "#D9E0FF";
        $purchasedPlan->is_free = false;

        $sumOfPurchasedPlanGames = 0;

        foreach($subscribedPlan as $activePlan){
            $plan = Plan::where('id', $activePlan->plan_id)->first();
            $data = new stdClass;
            $remainingGames = $plan->game_count - $activePlan->used_count;
            if($plan->is_free){
               
                $data->name = "Bonus Games";
                $data->description = $remainingGames. " games remaining" ;
                $data->background_color = "#FFFFFF";
                $data->is_free = $plan->is_free;

                if ( $remainingGames > 0){
                    $subscribedPlans[] = $data;
                }
            }else{
                $sumOfPurchasedPlanGames += $remainingGames;
            }

        }; 
        
        $purchasedPlan->description = $sumOfPurchasedPlanGames. " games remaining" ;
        
        if ( $sumOfPurchasedPlanGames > 0){
            $subscribedPlans[] = $purchasedPlan;
        }
        return $subscribedPlans;
    }

    public function hasPaidPlan(){
        $paid_plan = UserPlan::where('user_id', $this->id)->where('is_active',true)->where('plan_id','>',1)->first();
        if($paid_plan !== null){
            return true;
        }
        return false;
    }

    public function getChallengesPlayedAttribute()
    {
        return GameSession::where('user_id', $this->id)->where('game_mode_id', 2)->count();
    }

    public function getWinRateAttribute()
    {
        $gameWins = GameSession::where('correct_count', '>=', 5)->count();
        return ($gameWins / 100);
    }

    public function friends()
    {

        return User::where('id', '!=', $this->id)->get()->map(function ($friend) {
            $data = new stdClass;
            $data->id = $friend->id;
            $data->fullName = $friend->profile->full_name;
            $data->username = $friend->username;
            $data->avatar = $friend->profile->avatar;
            return $data;
        });
    }


    public function pointTransactions()
    {
        return $this->hasMany(UserPoint::class);
    }

    public function getUserPointTransactions()
    {
        return $this->pointTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
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

    public function userBoosts()
    {
        return DB::table('user_boosts')
            ->where('user_id', $this->id)
            ->join('boosts', function ($join) {
                $join->on('boosts.id', '=', 'user_boosts.boost_id');
            })->select('boosts.id','boosts.icon','boosts.description','name', 'user_boosts.boost_count as count')
            ->where('user_boosts.boost_count', '>', 0)->get();
    }
}
