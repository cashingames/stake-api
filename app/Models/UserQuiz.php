<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "title", "quiz_code", "description","category_id","avatar","is_public", "life_span"];


}
