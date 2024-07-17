<?php

use App\Exceptions\GatewayException;
use Illuminate\Foundation\Application;
use App\Exceptions\TransactionException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'تراکنش/درگاه مورد نظر یافت نشد.',
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (TransactionException $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'عملیات تراکنش با خطا مواجه شد',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $exceptions->render(function (GatewayException $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'عملیات درگاه با خطا مواجه شد',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'عملیات با خطا مواجه شد',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
