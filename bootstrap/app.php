<?php

use App\Models\ApplicationError;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');


        // Cache static assets (for php artisan serve; Apache uses .htaccess)
        $middleware->prepend(\App\Http\Middleware\CacheStaticAssets::class);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'nurse_approved' => \App\Http\Middleware\EnsureNurseIsApproved::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e) {
            if ($request->is('api/documentation*') || $request->is('docs/asset/*')) {
                return false;
            }
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

        // Validation – For API 
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/documentation*') || $request->is('docs/asset/*'))
                return null;
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }
            return ApiResponse::error('Validation failed', 422, $e->errors());
        });

        // Auth – For API 
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/documentation*') || $request->is('docs/asset/*'))
                return null;
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }
            return ApiResponse::error('Unauthenticated', 401);
        });

        // Model Not Found – API JSON, 
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/documentation*') || $request->is('docs/asset/*'))
                return null;
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }
            return ApiResponse::error('Resource not found', 404);
        });

        // Not Found Http Exception (e.g. invalid route or model not found converted)
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/documentation*') || $request->is('docs/asset/*'))
                return null;
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }
            return ApiResponse::error('Resource not found', 404);
        });

        /*  // Global catch – API JSON with error ID,
          $exceptions->render(function (Throwable $e, $request) {
              if ($request->is('api/documentation*') || $request->is('docs/asset/*')) return null;
              if (!$request->is('api/*') && !$request->expectsJson()) {
                  return null;
              }

              $errorId = 'ERR-' . strtoupper(str()->random(10));

              try {
                  ApplicationError::create([
                      'error_id' => $errorId,
                      'user_id' => auth()->id() ?? null,
                      'message' => $e->getMessage(),
                      'exception' => get_class($e),
                      'file' => $e->getFile(),
                      'line' => $e->getLine(),
                      'url' => $request->fullUrl(),
                      'method' => $request->method(),
                      'ip_address' => $request->ip(),
                      'request_data' => $request->all(),
                      'trace' => $e->getTraceAsString(),
                      'status' => ApplicationError::STATUS_PENDING,
                  ]);
              } catch (Throwable $logException) {
                  report($logException);
              }

              report($e);

              return ApiResponse::error('Something went wrong', 500, [
                  'error_id' => $errorId,
              ]);
          }); */
    })->create();
