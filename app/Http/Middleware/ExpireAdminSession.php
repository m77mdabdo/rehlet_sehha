<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drops a staff session after a period of inactivity.
 *
 * Laravel's SESSION_LIFETIME is global, so the panel cannot simply be given a
 * shorter one in config. This tracks last activity per request and signs the
 * user out once the gap exceeds the configured window.
 *
 * The panel needs a shorter window than the public site for a reason that has
 * nothing to do with the panel being more valuable: it is used on a shared
 * computer at a reception desk, left open between patients, in a room other
 * people walk through. A patient's own session exposes one appointment. This
 * one exposes every medical record the clinic holds.
 *
 * Idle time, not absolute age: a doctor working through a morning clinic is
 * not logged out mid-consultation, while the same screen left alone over lunch
 * is.
 */
class ExpireAdminSession
{
    private const KEY = 'admin.last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        $timeout = (int) config('clinic.admin_session_timeout_minutes', 30);

        if ($timeout <= 0 || ! Auth::check()) {
            return $next($request);
        }

        $lastActivity = $request->session()->get(self::KEY);

        if (is_int($lastActivity) && (time() - $lastActivity) > ($timeout * 60)) {
            Auth::logout();

            /*
             * The session is invalidated and the token regenerated, not merely
             * emptied: leaving the old session id valid would let anyone who
             * had captured the cookie carry on using it.
             */
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->guest(filament()->getLoginUrl());
        }

        $request->session()->put(self::KEY, time());

        return $next($request);
    }
}
