<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Утилита для единообразного JSON-ответа
        $jsonResponse = function (string $message, int $code, ?array $extra = []): JsonResponse {
            $body = ['error' => $message];
            if ($extra) {
                $body = array_merge($body, $extra);
            }
            return response()->json($body, $code);
        };

        // Кастомное API-исключение
        $exceptions->renderable(function (ApiException $e) use ($jsonResponse) {
            return $jsonResponse($e->getMessage(), $e->getCode());
        });
        $exceptions->dontReport(ApiException::class);

        // 401 – JWT проблемы
        $exceptions->renderable(function (JWTException $e) use ($jsonResponse) {
            report($e);

            $message = match (true) {
                $e instanceof TokenExpiredException => trans('auth.jwt.token_expired'),
                $e instanceof TokenInvalidException => trans('auth.jwt.token_invalid'),
                default => trans('auth.jwt.token_general'),
            };

            return $jsonResponse($message, Response::HTTP_UNAUTHORIZED);
        });

        // 401 – Требуется войти в систему
        $exceptions->renderable(function (AuthenticationException $e, Request $request) use ($jsonResponse) {
            return $jsonResponse(
                $e->getMessage() ?: trans('auth.unauthenticated'),
                Response::HTTP_UNAUTHORIZED
            );
        });

        // 403 – Доступ запрещён
        $exceptions->renderable(function (AuthorizationException $e) use ($jsonResponse) {
            return $jsonResponse(
                $e->getMessage() ?: trans('auth.forbidden'),
                Response::HTTP_FORBIDDEN
            );
        });

        // 404 – Модель/ресурс не найден
        $exceptions->renderable(function (NotFoundHttpException $e) use ($jsonResponse) {
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException) {
                // Можно извлечь имя модели, но не обязательно
                return $jsonResponse(trans('exceptions.not_found'), Response::HTTP_NOT_FOUND);
            }
            return $jsonResponse(trans('exceptions.page_not_found'), Response::HTTP_NOT_FOUND);
        });

        // 422 – Ошибки валидации (унифицированный формат)
        $exceptions->renderable(function (ValidationException $e) use ($jsonResponse) {
            return $jsonResponse(
                trans('exceptions.validation_failed'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['errors' => $e->errors()]
            );
        });

        // 429 – Слишком много запросов
        $exceptions->renderable(function (ThrottleRequestsException $e) use ($jsonResponse) {
            return $jsonResponse(
                $e->getMessage() ?: trans('exceptions.too_many_requests'),
                Response::HTTP_TOO_MANY_REQUESTS
            );
        });

        // База данных – не раскрываем детали
        $exceptions->renderable(function (QueryException $e) use ($jsonResponse) {
            report($e); // логируем с деталями
            return $jsonResponse(
                trans('exceptions.internal_error'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });

        // Любые другие неожиданные исключения
        $exceptions->renderable(function (Throwable $e) use ($jsonResponse) {
            report($e);
            return $jsonResponse(
                config('app.debug') ? $e->getMessage() : trans('exceptions.internal_error'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });

        // Не логируем стандартные HTTP-ошибки (они не критичны)
        $exceptions->dontReport([
            AuthenticationException::class,
            AuthorizationException::class,
            NotFoundHttpException::class,
            ValidationException::class,
            ThrottleRequestsException::class,
        ]);
    })->create();
