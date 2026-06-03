<?php

use App\Domains\Booking\Exceptions\BookingConflictException;
use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Exceptions\CustomerNotFoundException;
use App\Domains\Booking\Exceptions\NoTablesAvailableException;
use App\Shared\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/webhook.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => null);
        $middleware->validateCsrfTokens(except: [
            'webhook/telegram/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.', 'code' => 401], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden.', 'code' => 403], 403);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                    'code' => 422,
                ], 422);
            }
        });

        $exceptions->render(function (BookingNotFoundException|CustomerNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage(), 'code' => 404], 404);
            }
        });

        $exceptions->render(function (BookingConflictException|NoTablesAvailableException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage(), 'code' => 422], 422);
            }
        });

        $exceptions->render(function (TransitionNotFound $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Invalid state transition.', 'code' => 422], 422);
            }
        });

        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage(), 'code' => 422], 422);
            }
        });
    })->create();
