<?php
/** @noinspection PhpUndefinedClassInspection */

namespace SlimHttpAbstracts\ControllerAbstracts;

use DI\Container;
use Exception;
use Monolog\Logger;
use Slim\Exception\MethodNotAllowedException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use SlimHttpAbstracts\Response\JsonResponse;

/**
 * Class ControllerAbstractHelper
 * @package SlimHttpAbstracts\ControllerAbstracts
 */
class CoreControllerAbstract
{
    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var Logger
     */
    protected $logger = null;

    /**
     * @var array
     */
    protected $decodedBody = null;

    /**
     * The list of allowed HTTP methods.
     *
     * @var string[]
     */
    protected $allowedMethods = [
        'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'PATCH'
    ];

    /**
     * CoreControllerAbstract constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Called before executing the controller action.
     */
    protected function preDispatch()
    {
    }

    /**
     * Called after executing the controller action.
     */
    protected function postDispatch()
    {
    }

    /**
     * Set the request object
     *
     * @param Request $request
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Set the response object
     *
     * @param Response $response
     */
    protected function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Set the parsed URL arguments (not query parameters, actual route components).
     *
     * @param array $arguments
     */
    protected function setArguments(array $arguments)
    {
        $this->args = $arguments;
    }

    /**
     * Get a query parameter.
     *
     * @param string $name
     * @param mixed|null $default (optional) default null
     * @return mixed
     */
    protected function getQueryParam($name, $default=null)
    {
        $params = $this->getRequest()->getQueryParams();
        if (!array_key_exists($name, $params)) {
            return $default;
        }
        if (!is_string($params[$name]) || mb_strlen($params[$name]) === 0) {
            return $default;
        }
        return $params[$name];
    }

    /**
     * Get a $_SERVER parameter.
     *
     * @param string $name
     * @param mixed|null $default (optional) default null
     * @return mixed
     */
    protected function getServerParam($name, $default=null)
    {
        $params = $this->getRequest()->getServerParams();
        if (!array_key_exists($name, $params)) {
            return $default;
        }
        return $params[$name];
    }

    /**
     * Get the request object.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object.
     *
     * @return Response
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * Get all arguments (route parameters).
     *
     * @return array
     */
    protected function getArguments()
    {
        return $this->args;
    }

    /**
     * Get a single argument (route parameter).
     *
     * @param string $key
     * @return string|null
     */
    protected function getArgument($key)
    {
        if (array_key_exists($key, $this->getArguments())) {
            if (strlen($this->getArguments()[$key]) === 0) {
                return null;
            }
            return $this->getArguments()[$key];
        }
        return null;
    }

    /**
     * Does a specific argument exist?
     *
     * @param string $key
     * @return bool
     */
    protected function hasArgument($key)
    {
        return $this->getArgument($key) !== null;
    }

    /**
     * Get the container.
     *
     * @return \DI\Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Get a single item from the container.
     *
     * @param string $id
     * @return mixed
     */
    protected function getContainerItem($id)
    {
        try {
            return $this->container->get($id);
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            try {
                $this->logger = $this->container->get('logger');
            } catch (Exception $e) {
            }
        }
        return $this->logger;
    }

    /**
     * Get the decoded JSON body. Returns array on success, null on failure.
     *
     * @return array|null
     */
    protected function getDecodedJsonBody()
    {
        if (!in_array($this->getRequest()->getMethod(), ['PUT', 'POST'])) {
            return null;
        }
        if ($this->decodedBody !== null) {
            return $this->decodedBody;
        }
        $content = @json_decode(
            $this->getRequest()->getBody()->__toString(),
            true
        );
        if (!$content) {
            return null;
        }
        $this->decodedBody = $content;

        return $this->decodedBody;
    }

    /**
     * Get a specific key from the JSON body.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    protected function getJsonBodyParam($key, $default=null)
    {
        if (null === $decoded = $this->getDecodedJsonBody()) {
            return $default;
        }
        if (!array_key_exists($key, $decoded)) {
            return $default;
        }
        if (null === $decoded[$key] || (empty($decoded[$key]) && $decoded[$key] !== 0)) { // zero is empty.
            return $default;
        }
        return $decoded[$key];
    }

    /**
     * Redirect this request, optionally providing a status code for the redirection.
     *
     * @param string $url
     * @param int $status (optional) default 302
     * @return Response
     */
    protected function redirect($url, $status=302)
    {
        return $this->getResponse()->withHeader(
            'Location',
            $url
        )->withStatus(
            $status
        );
    }

    /**
     * @throws MethodNotAllowedException
     */
    protected function methodNotAllowed()
    {
        return $this->json(
            ['error' => 'Method not allowed'],
            405
        );
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function get()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function post()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function put()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function delete()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @return Response
     */
    protected function options()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function head()
    {
        return $this->methodNotAllowed();
    }

    /**
     * @throws MethodNotAllowedException
     * @return Response
     */
    protected function patch()
    {
        return $this->methodNotAllowed();
    }

    /**
     * Send a JSON response.
     *
     * @param array|object $payload
     * @param int $status (optional) default 200
     * @param int $encodingOptions (optional) default 0, for example `JSON_PRETTY_PRINT`
     * @return Response
     */
    protected function json($payload, $status=200, $encodingOptions=0)
    {
        $this->setResponse(
            JsonResponse::withJson(
                $payload,
                $status,
                $encodingOptions
            )
        );
        return $this->response;
    }

    /**
     * Dispatch an incoming routed request. This must be overridden in inheriting classes to return a CORS
     * compatible response.
     *
     * @param string $name
     * @param array $arguments
     * @return Response|void
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (!$arguments[0] instanceof Request || !$arguments[1] instanceof Response) {
            throw new Exception(
                sprintf(
                    'Missing argument parameter in %s::%s',
                    __CLASS__,
                    $name
                )
            );
        }
        $this->setRequest($arguments[0]);
        $this->setResponse($arguments[1]);
        $this->setArguments($arguments[2]);

        $method = $this->getRequest()->getMethod();

        if (!in_array($method, $this->allowedMethods)) {
            return $this->methodNotAllowed();
        }

        $this->preDispatch();
        $this->{strtolower($method)}();
        $this->postDispatch();
        return $this->response;
    }
}
