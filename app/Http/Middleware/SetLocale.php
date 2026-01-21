<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from Accept-Language header or query parameter
        $locale = $request->header('Accept-Language')
            ?? $request->query('lang')
            ?? config('app.locale', 'ar');

        // Clean locale (remove region code if present, e.g., 'ar-SA' -> 'ar')
        $locale = strtolower(substr($locale, 0, 2));

        // Validate locale (only allow 'ar' or 'en')
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'ar'; // Default to Arabic
        }

        App::setLocale($locale);

        return $next($request);
    }
}
