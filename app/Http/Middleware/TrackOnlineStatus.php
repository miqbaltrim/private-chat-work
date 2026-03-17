<?php
// app/Http/Middleware/TrackOnlineStatus.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackOnlineStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Update every 5 minutes to reduce DB writes
            if (!$user->last_seen || $user->last_seen->diffInMinutes(now()) >= 5) {
                $user->update([
                    'is_online' => true,
                    'last_seen' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
