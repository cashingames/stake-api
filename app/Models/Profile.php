<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

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

    public function user(){
        return $this->belongsTo(User::class);
    }

}
