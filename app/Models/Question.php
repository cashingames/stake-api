<?php

namespace App\Models;

use App\Enums\QuestionLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = [
        'options'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'game_id',
        'created_at',
        'updated_at'
    ];

    protected $fillable = ['created_by', 'is_published'];
    //
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('published', function (Builder $builder) {
            $builder->where('is_published', true);
        });
    }

    public function options()
    {
        return $this->hasMany(Option::class)->inRandomOrder();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'categories_questions', 'question_id', 'category_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function scopeEasy($query)
    {
        return $query->whereLevel(QuestionLevel::Easy);
    }
    public function scopeEasyOrMedium($query)
    {
        return $query->whereLevel(QuestionLevel::Easy)->orWhere('level', QuestionLevel::Medium);
    }

    public function scopeMedium($query)
    {
        return $query->whereLevel(QuestionLevel::Medium);
    }

    public function scopeHardOrMedium($query)
    {
        return $query->whereLevel(QuestionLevel::Hard)->orWhere('level', QuestionLevel::Medium);
    }

    public function scopeHard($query)
    {
        return $query->whereLevel(QuestionLevel::Hard);
    }

    public function scopeExpert($query)
    {
        return $query->whereLevel(QuestionLevel::Expert);
    }
}
