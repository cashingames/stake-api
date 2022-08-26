<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogLastActivityTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guest()){
            return $next($request);
        }
        
        // auth()->user()->update(['last_activity_time' => now()]);
        $user = auth()->user();
        $user->last_activity_time = now();
        $user->save();
        Log::info("last user activity time for $user->name is: " . now());
        return $next($request);
        
    }
}
