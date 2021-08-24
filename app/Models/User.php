<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

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
        'points'
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
        'points'=>'integer'
    ];

    protected $appends = [
        'achievement'
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
        return $this->hasManyThrough(WalletTransaction::class, Wallet::class)->orderBy('created_at', 'desc');
    }
    
    public function points(){
        return $this->hasMany(UserPoint::class);
    }

    public function boosts(){
        return $this->hasMany(UserBoost::class);
    }

    public function achievement(){
        return $this->hasOne(Achievement::class);
    }

    public function quizzes(){
        return $this->hasMany(UserQuiz::class);
    }

    public function challenges(){
        return $this->hasMany(Challenge::class);
    }

    public function getAchievementAttribute()
    {   
        $latestAchievement = DB::table('user_achievements')
        ->where('user_id', $this->id)->latest()->first();

        if( $latestAchievement === null){
            return " ";
        }
        $achievement = Achievement::where('id',$latestAchievement->achievement_id)->first();
        
        return($achievement->title);
        
    }
}
