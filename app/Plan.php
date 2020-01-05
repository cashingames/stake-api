<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    //
    public function users(){
        return $this->hasMany(User::class);
    }

    public function games(){
        return $this->hasMany(Game::class);
    }
}
