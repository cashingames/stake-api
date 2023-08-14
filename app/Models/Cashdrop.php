<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Cashdrop extends Model
{
    use HasFactory;

    public function rounds(): HasMany
    {
        return $this->hasMany(CashdropRound::class);
    }
}
