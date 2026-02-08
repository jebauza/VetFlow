<?php

use Illuminate\Http\Request;
use App\Common\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Modules\Auth\Exceptions\LoginFailedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(function (AuthenticationException|LoginFailedException $e, $request) {
            if ($request->is('api/*')) {
                $errors = ['auth' => [__('Authentication token is invalid or expired')]];
                if ($e instanceof LoginFailedException) {
                    $errors = ['credentials' => [$e->getMessage()]];
                }

                return ApiResponse::error(
                    __('Unauthorized'),
                    401,
                    $errors
                );
            }
        });

        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('Forbidden'),
                    403,
                    ['auth' => [__('You do not have permission to access this resource')]]
                );
            }
        });

        $exceptions->renderable(function (NotFoundHttpException|ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    __('Not Found'),
                    404,
                    ['resource' => [__('The requested resource does not exist')]]
                );
            }
        });

        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::validation($e->errors());
            }
        });

        $exceptions->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage() ?: __('HTTP error'),
                    $e->getStatusCode()
                );
            }
        });
    })->create();
