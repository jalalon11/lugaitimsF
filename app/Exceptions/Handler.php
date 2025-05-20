<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
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
            // Log all exceptions with detailed information
            Log::error('Exception: ' . get_class($e) . ' - ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        // Handle database query exceptions
        $this->renderable(function (QueryException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Database error occurred. Please try again later.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Database error occurred. Please try again later.');
        });

        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                    'error' => $e->getMessage(),
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'The requested resource was not found.');
        });

        // Handle 404 errors
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'The requested page was not found.',
                    'error' => $e->getMessage(),
                ], 404);
            }

            return redirect()->route('user.loginPage')
                ->with('error', 'The requested page was not found.');
        });

        // Handle all other exceptions
        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof QueryException || $e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return null; // Let the specific handlers above handle these
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'An error occurred. Please try again later.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'An error occurred. Please try again later.');
        });
    }

}
