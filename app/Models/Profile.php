<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'state',
        'referral_code',
        'account_name',
        'account_number',
        'bank_name',
        'avatar',
        'referrer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReferrerProfile()
    {
        if ($this->referrer == null || trim($this->referrer) == '') {
            return null;
        }

        $profile = null;
        if ($profileRefferrer = Profile::where('referral_code', $this->referrer)->first()) {
            $profile = $profileRefferrer;
        } elseif ($user = User::where('username', $this->referrer)->first()) {
            $profile = $user->profile;
        }

        return $profile;
    }

    public function scopeReferrals()
    {
        return Profile::where('referrer', $this->referral_code);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }
}
