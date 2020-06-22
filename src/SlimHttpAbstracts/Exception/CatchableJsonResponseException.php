<?php
namespace SlimHttpAbstracts\Exception;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Throwable;

/**
 * Class CatchableJsonResponseException
 * @package SlimHttpAbstracts\Exception
 */
abstract class CatchableJsonResponseException extends Exception
{
    /**
     * @var int|null
     */
    private $statusCode = null;

    /**
     * CatchableJsonResponseException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $continue = StatusCodeInterface::STATUS_CONTINUE;
        $authRequired = StatusCodeInterface::STATUS_NETWORK_AUTHENTICATION_REQUIRED;
        if ($code >= $continue || $code <= $authRequired) {
            $this->statusCode = $code;
        } else {
            $this->statusCode = 500;
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the status code for a response.
     *
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getMessage();
    }
}
