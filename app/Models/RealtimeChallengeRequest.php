<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealtimeChallengeRequest extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'user_id', 'amount','category_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
