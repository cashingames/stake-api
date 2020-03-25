<?php

namespace App;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\PasswordResetNotification;
use App\Wallet;

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
        'show_bonus'
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
        if ( !empty($password) ) {
            $this->attributes['password'] = bcrypt($password);
        }
    }

    public function profile(){
        return $this->hasOne(Profile::class);
    }

    public function wallet(){
        return $this->hasOne(Wallet::class);
    }

    public function transactions(){
        return $this->hasManyThrough(WalletTransaction::class, Wallet::class);
    }

    public function plans(){
        return $this->belongsToMany(Plan::class, 'user_plan')->withPivot('used','id');
    }

    public function games(){
        return $this->hasMany(Game::class);
    }

    public function activePlans(){
        return $this->plans()->wherePivot('is_active', true);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    public function getShowBonusAttribute(){
        return $this->wallet->bonus == 150;
    }

}
