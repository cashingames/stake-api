<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashdropRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashdrop_id','pooled_amount', 'dropped_at','percentage_stake',
        'created_at', 'updated_at'
    ];

}
