<?php
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckUserActive;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function (Middleware $middleware): void {
        // Optionally keep Sanctum
        // The 'prepend' option adds middleware to the beginning of the API middleware stack.
                $middleware->api(prepend: [
                    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                ]);

        // Redirect guests to '/up' for custom guest handling
                $middleware->redirectGuestsTo(fn () => '/up');
        $middleware->redirectGuestsTo(fn ($req) => '/up');

        // Add your middleware alias
        $middleware->alias([
            'active' => CheckUserActive::class,
            // other aliases...
        ]);

        // (Optional) Apply to all API routes globally:
        // $middleware->api(append: [CheckUserActive::class]);
    })
    // ...
    ->create();
