<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GatewayException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error(formatExceptionMessage($this));
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $this->getMessage() : 'عملیات درگاه با خطا مواجه شد',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
