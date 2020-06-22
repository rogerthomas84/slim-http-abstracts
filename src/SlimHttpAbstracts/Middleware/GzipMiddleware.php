<?php
namespace SlimHttpAbstracts\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

/**
 * Class GzipMiddleware
 * @package SlimHttpAbstracts\Middleware
 */
class GzipMiddleware
{
    /**
     * Process the remaining middleware stack and establish whether the response should be issues
     * with gzip compression.
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface|Response
     */
    public function __invoke(Request $request, RequestHandlerInterface $handler)
    {
        if (stristr($request->getHeaderLine('Accept-Encoding'), 'gzip') === false) {
            // Browser doesn't accept gzip compression
            return $handler->handle(
                $request
            );
        }

        // Compress response data
        $deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
        $response = $handler->handle($request);
        $compressed = deflate_add($deflateContext, (string)$response->getBody(), \ZLIB_FINISH);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $compressed);
        rewind($stream);
        return $response
            ->withHeader('Content-Encoding', 'gzip')
            ->withHeader('Content-Length', strlen($compressed))
            ->withBody(new Stream($stream));
    }
}
