<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromBrowser
{
    /** @var string[] */
    private const SUPPORTED_LOCALES = ['pt', 'en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        $response = $next($request);

        if ($request->has('lang')) {
            $response->headers->setCookie(cookie('locale', $locale, 60 * 24 * 365));
        }

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Query param ?lang=
        if ($lang = $request->query('lang')) {
            if (in_array($lang, self::SUPPORTED_LOCALES, true)) {
                return $lang;
            }
        }

        // 2. Cookie
        if ($cookie = $request->cookie('locale')) {
            if (in_array($cookie, self::SUPPORTED_LOCALES, true)) {
                return $cookie;
            }
        }

        // 3. Accept-Language header
        $preferred = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
        if ($preferred) {
            return $preferred;
        }

        return config('app.locale', 'en');
    }
}
