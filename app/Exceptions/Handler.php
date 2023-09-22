<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof QueryException) {
            return $this->errorResponse($e->getMessage(), 500);
        }
        if ($e instanceof HttpException) {
            return $this->errorResponse($e->getMessage(), 419);
        }

        if($e instanceof MassAssignmentException){
            return $this->errorResponse($e->getMessage(), 500);
        }

        if($e instanceof NotFoundHttpException){
            return $this->errorResponse($e->getMessage(), 404);
        }

        if($e instanceof NotFoundHttpException){
            return $this->errorResponse($e->getMessage(), 404);
        }
        return parent::render($request, $e);
    }
}
