<?php

if (!function_exists('formatExceptionMessage')) {
    /**
     * Formats an exception message into a string for logging.
     *
     * @param Exception|Throwable $e The exception object to format the message for.
     *
     * @return string The formatted exception message containing the exception details.
     */
    function formatExceptionMessage(Exception|Throwable $e): string {
        return class_basename($e) . ' => Message: ' . $e->getMessage() . ' | Code: ' . $e->getCode() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine() . ' | Trace: ' . json_encode($e->getTrace());
    }
}
