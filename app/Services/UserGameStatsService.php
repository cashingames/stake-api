<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GameSession;
use App\Models\User;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserGameStatsService
{
    use DateUtils;
    public $userName;
    public $winRate;
    public $gamePlayed;
    public $correctCountAverage;
    public $maxCategory;
    public $userTotalBoost;

    public function getBiWeeklyUserGameStats(User $user)
    {
        $startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::now()->subWeeks(2)->startOfDay());
        $endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::now());
        
        $this->userName = $user->username;
        $this->winRate = $user->win_rate;
        $this->gamePlayed = GameSession::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->count();

        $totalQuestions = GameSession::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->count();

        $totalCorrectAnswers = GameSession::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('correct_count');

        $this->correctCountAverage = ($totalQuestions/$totalCorrectAnswers) * 100;

        $this->userTotalBoost = DB::table('user_boosts')
            ->where('user_id', $user->id)
            ->join('boosts', function ($join) {
                $join->on('boosts.id', '=', 'user_boosts.boost_id');
            })->select('boosts.id', 'boosts.point_value', 'boosts.pack_count', 'boosts.currency_value', 'boosts.icon', 'boosts.description', 'name', 'user_boosts.boost_count as count')
            ->whereNull('boosts.deleted_at')
            ->where('user_boosts.boost_count', '>', 0)
            ->sum('user_boosts.boost_count');

        $subcategories = Category::all();
        $categoryMaxCount = [];
        foreach ($subcategories as $category) {
            $categoryCount = $user->gameSessions()
                ->where('category_id', $category->id)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->count();

            $categoryMaxCount[] = [
                'name' => $category->name,
                'count' => $categoryCount,
            ];
        }

        $maxCount = 0;
        $this->maxCategory = null;

        foreach ($categoryMaxCount as $category) {
            if ($category['count'] > $maxCount) {
                $maxCount = $category['count'];
                $this->maxCategory = $category;
            }
        }
        $data = [
            'username' => $this->userName,
            'win_rate' => $this->winRate,
            'gamePlayed' => $this->gamePlayed,
            'correctCountAverage' => round($this->correctCountAverage),
            'category' => $this->maxCategory,
            'availableBoost' => $this->userTotalBoost,
        ];
        return $data;
    }
}
