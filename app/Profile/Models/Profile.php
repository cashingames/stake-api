<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'gender', 'date_of_birth', 'address', 'state', 'account_name', 'avatar','bank_name','account_number','currency'
    ];

    //
    public function user(){
        return $this->belongsTo(User::class);
    }
}
