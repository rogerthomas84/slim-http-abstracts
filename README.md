Slim Framework 4 Abstracts
==========================

### Using the AppHtmlErrorRenderer

Depending on your error settings, the `AppHtmlErrorRenderer` can show different levels of error pages.

The only real difference is the ability to change the title of the page from `Slim Application` to a custom name.

```php
<?php
// set up slim:
$app = AppFactory::create();
$app->addRoutingMiddleware();

// ...
// ...

$errorMiddleware = $app->addErrorMiddleware(
    true, // Display errors or false to hide them
    true, // Log errors
    true // Log error details
);


$errorHandler = function (
    ServerRequestInterface $request,
    Throwable $e,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    $logger = null
) use ($app) {
    $renderer = new \SlimHttpAbstracts\Error\Renderers\AppHtmlErrorRenderer();
    $renderer->setDefaultErrorTitle('My Application Name');

    $body = $renderer->__invoke($e, $displayErrorDetails);
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        $body
    );
    return $response;
};
$errorMiddleware->setDefaultErrorHandler($errorHandler);

```