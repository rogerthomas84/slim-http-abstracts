<?php
namespace SlimHttpAbstracts\Middleware;

use DI\Container;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use SlimHttpAbstracts\Exception\CatchableJsonResponseException;
use SlimHttpAbstracts\Response\JsonResponse;
use Throwable;

/**
 * Class JsonErrorHandlingMiddleware
 * @package SlimHttpAbstracts\Middleware
 * @example
 * \SlimHttpAbstracts\Middleware\JsonErrorHandlingMiddleware::setup(
 *      \Slim\App $app,
 *      \DI\Container $container,
 *      bool $displayErrorDetails,
 *      bool $logErrors,
 *      bool $logErrorDetails
 * );
 */
class JsonErrorHandlingMiddleware
{
    /**
     * Setup and bind a JSON based error handler against the app.
     *
     * @param App $app
     * @param Container $container
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     */
    public static function setup(App $app, Container $container, $displayErrorDetails, $logErrors, $logErrorDetails)
    {
        $errorMiddleware = $app->addErrorMiddleware(
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        );
        $errorMiddleware->setDefaultErrorHandler(
            self::createErrorHandler($app, $container)
        );
    }

    /**
     * Get a correctly formed JSON error handler for use in the middleware.
     *
     * @param App $app
     * @param Container $container
     * @return \Closure
     */
    protected static function createErrorHandler(App $app, Container $container)
    {
        /**
         * @todo This could be bound to an abstract Exception to control response messages.
         *
         * @param ServerRequestInterface $request
         * @param Throwable $exception
         * @param bool $displayErrorDetails
         * @param bool $logErrors
         * @param bool $logErrorDetails
         * @return \Psr\Http\Message\ResponseInterface
         */
        return function (
            ServerRequestInterface $request,
            Throwable $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        ) use ($app, $container) {
            $context = [];
            if ($logErrorDetails === true) {
                $context = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }

            if ($logErrors === true) {
                $errorMessage = sprintf(
                    'Error: %s in %s::%s',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                );
                if ($container->has('logger')) {
                    $logger = $container->get('logger');
                    /* @var $logger Logger */
                    $logger->addError(
                        $errorMessage,
                        $context
                    );
                } else {
                    @error_log($errorMessage);
                }
            }

            $payload = ['error' => 'Unknown error'];
            if ($displayErrorDetails === true) {
                $payload = [
                    'error' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace()
                ];
            }
            $encodingOptions = 0;
            if ($displayErrorDetails === true) {
                $encodingOptions = JSON_PRETTY_PRINT;
            }

            if ($exception instanceof CatchableJsonResponseException) {
                $payload['error'] = $exception->getErrorMessage();
                return JsonResponse::withJson(
                    $payload,
                    $exception->getStatusCode(),
                    $encodingOptions
                );
            }

            return JsonResponse::withJson(
                $payload,
                500,
                $encodingOptions
            );
        };
    }
}
