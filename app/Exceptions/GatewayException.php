<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GatewayException extends Exception
{
    /**
     * @return string The prepared log message for the current exception.
     */
    private function getLogMessage(): string
    {
        return 'GatewayException => Message: ' . $this->message . ' | Code: ' . $this->getCode() . ' | File: ' . $this->getFile() . ' | Line: ' . $this->getLine();
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error($this->getLogMessage());
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
