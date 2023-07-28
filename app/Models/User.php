<?php

namespace App\Models;

use App\Models\Boost;
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
    ];

    protected $appends = [
        'full_phone_number'
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

    public function boosts()
    {
        return $this->belongsToMany(Boost::class, 'user_boosts')
            ->withPivot('boost_count', 'used_count')
            ->withTimestamps();
    }

    public function getFullPhoneNumberAttribute()
    {
        return $this->country_code . $this->phone_number;
    }


    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function getAverageOfRecentGames()
    {
        return $this->gameSessions()
            ->completed()
            ->latest()
            ->limit(2)
            ->get()
            ->avg('correct_count');

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
            })->select(
                'boosts.id',
                'boosts.pack_count',
                'boosts.currency_value',
                DB::raw("boosts.currency_value * boosts.pack_count as price"),
                'boosts.icon',
                'boosts.description',
                'name',
                'user_boosts.boost_count as count'
            )
            ->whereNull('boosts.deleted_at')
            ->where('name', '!=', 'Bomb')
            ->where('user_boosts.boost_count', '>', 0)->get();
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
