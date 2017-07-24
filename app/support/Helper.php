<?php

namespace EComConnBroker\Support;

class Helper
{
    /**
     * Helper method to log request.
     */
    public static function logRequest()
    {
        // Check if debug logging is enabled
        if (!self::getDI('config')->app->debug)
            return;

        // Parse request
        $userIp = $_SERVER['REMOTE_ADDR'];
        $executionTime = (microtime(true) - self::getDI('start_time')->getValue());
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A';
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'N/A';
        $requestBody = file_get_contents('php://input');
        if (!$requestBody || empty($requestBody))
            $requestBody = sizeof($_POST) ? print_r($_POST, true) : null;

        // Log request
        $logger = self::getDI('debug_logger');
        $logger->begin();
        $logger->debug(sprintf(
            "Processed request for %s in %f seconds.\r\n" .
            "Request Uri: %s\r\n" .
            (!empty($requestBody) ? "Request Body: %s\r\n" : ''),
            $userIp,
            $executionTime,
            $requestMethod ." ". $requestUri,
            trim($requestBody)));
        $logger->commit();
    }

    /**
     * Method to log the exception and send it as json response to client.
     *
     * @param $exception
     * @return mixed
     */
    public static function handleException($exception)
    {
        // Log unhandled exception as an error
        $logger = self::getDI('error_logger');
        $logger->begin();
        $logger->error($exception->getMessage());
        $logger->debug($exception->getFile() . ':' . $exception->getLine());
        $logger->debug("StackTrace:\r\n" . $exception->getTraceAsString() . "\r\n");
        $logger->commit();

        // Parse status code
        $statusCode = 500;
        if (is_a($exception, 'EComConnBroker\\Exceptions\\ApiException')) {
            $statusCode = $exception->getStatusCode();
        }

        // Parse error message
        $errorMessage = $exception->getMessage();
        if (self::getDI('config')->app->debug) {
            $errorMessage .= ' @ ' . basename($exception->getFile()) . ':' . $exception->getLine();
        }

        // Send error response
        return self::getDI('json_response')
            ->sendError($errorMessage, $statusCode);
    }

    /**
     * Internal helper method to retrieve default di.
     *
     * @param $type
     * @return mixed
     */
    private static function getDI($type)
    {
        return \Phalcon\DI::getDefault()->get($type);
    }
}
