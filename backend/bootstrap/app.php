<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API uses Bearer tokens — never redirect unauthenticated API clients to a web login route.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'البيانات المدخلة غير صحيحة.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => 'يجب تسجيل الدخول للمتابعة.'], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json(['message' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.'], 403);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $fallback = match ($status) {
                403 => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.',
                404 => 'المورد المطلوب غير موجود.',
                429 => 'محاولات كثيرة، حاولي لاحقاً.',
                default => $status >= 500 ? 'حدث خطأ في الخادم، حاولي لاحقاً.' : ($e->getMessage() ?: 'طلب غير ناجح.'),
            };

            return response()->json([
                'message' => $e->getMessage() !== '' && $status < 500 ? $e->getMessage() : $fallback,
            ], $status);
        });
    })->create();
