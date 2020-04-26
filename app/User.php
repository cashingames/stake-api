<?php

namespace App;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\PasswordResetNotification;
use App\Wallet;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'email', 'phone', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'first_login', 'password_token', 'token_expiry',
        'created_at', 'updated_at', 'phone_verified_at', 'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'show_bonus' => 'boolean',
        'first_login' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'show_bonus', 'lite_client', 'rank'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = bcrypt($password);
        }
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

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'user_plan')->withPivot('used', 'id');
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function activePlans()
    {
        return $this->plans()->wherePivot('is_active', true);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    public function getShowBonusAttribute()
    {
        return $this->wallet->bonus == 150;
    }

    public function getLiteClientAttribute()
    {
        return config('app.use_lite_client');
    }

    public function getRankAttribute()
    {
        $results = DB::select(
            'select SUM(points_gained) as score, user_id from games
            group by user_id
            order by score desc
            limit 100'
        );

        $user_index = 0;
        if (count($results) > 0) {
            $user_index = collect($results)->search(function ($user) {
                return $user->user_id == $this->id;
            });
        }

        if ($user_index === false)
            return 786;

        return $user_index + 1;
    }

}
