<?php
namespace SlimHttpAbstracts\Response;

use Slim\Psr7\Response;

/**
 * Class JsonResponse
 * @package SlimHttpAbstracts\Response
 */
class JsonResponse
{
    /**
     * Construct a PSR-7 compatible JSON response.
     *
     * @param array|object $payload
     * @param int $status
     * @param int $encodingOptions
     * @return mixed
     */
    public static function withJson($payload, $status=200, $encodingOptions=0)
    {
        $response = new Response();
        $response->getBody()->write(
            json_encode($payload, $encodingOptions)
        );
        return $response->withHeader(
            'Content-Type',
            'application/json'
        )->withStatus(
            $status
        );
    }
}
