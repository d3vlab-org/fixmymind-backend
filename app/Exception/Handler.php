<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        // Jeśli to ValidationException, sformatuj poprawnie
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'message' => 'Błąd walidacji.',
                'errors' => $e->errors(),
            ], $e->status);
        }

        // Jeśli to NotFoundHttpException (404)
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'message' => 'Nie znaleziono zasobu.',
            ], 404);
        }

        // Domyślny fallback JSON
        return response()->json([
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => config('app.debug') ? collect($e->getTrace())->take(5) : [],
        ], $status);
    }
}
