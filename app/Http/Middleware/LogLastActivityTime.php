<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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

        $user = auth()->user();
        $user->last_activity_time = now();
        $user->save();

        return $next($request);
        
    }
}
