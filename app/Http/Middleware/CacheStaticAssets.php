<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds long-lived Cache-Control headers for static assets
 * served through PHP's built-in server (php artisan serve).
 *
 * On Apache/Nginx in production, .htaccess or server config
 * handles this more efficiently. This middleware is only
 * needed for local development.
 */
class CacheStaticAssets
{
    /**
     * File extensions to cache and their max-age in seconds.
     */
    protected array $cacheMap = [
        // CSS & JS — 1 year (versioned via ?v=)
        'css'   => 31536000,
        'js'    => 31536000,
        // Fonts — 1 year
        'woff'  => 31536000,
        'woff2' => 31536000,
        'ttf'   => 31536000,
        'eot'   => 31536000,
        // Images — 6 months
        'svg'   => 15768000,
        'png'   => 15768000,
        'jpg'   => 15768000,
        'jpeg'  => 15768000,
        'gif'   => 15768000,
        'webp'  => 15768000,
        'ico'   => 31536000,
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $path = $request->getPathInfo();
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (isset($this->cacheMap[$ext])) {
            $maxAge = $this->cacheMap[$ext];
            $response->headers->set('Cache-Control', "public, max-age={$maxAge}, immutable");
        }

        return $response;
    }
}
