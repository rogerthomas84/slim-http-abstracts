<?php
/** @noinspection PhpUndefinedClassInspection */

namespace SlimHttpAbstracts\ControllerAbstracts;

use DateTime;
use Slim\Exception\MethodNotAllowedException;
use Slim\Psr7\Response;

/**
 * Class ApiCoreControllerAbstract
 * @package SlimHttpAbstracts\ControllerAbstracts
 */
class ApiCoreControllerAbstract extends CoreControllerAbstract
{
    /**
     * Send a JSON invalid request response.
     *
     * @param string|null $message
     * @return Response
     */
    public function invalidRequest($message=null)
    {
        if ($message === null) {
            $message = 'Invalid request';
        }
        return $this->json(
            [
                'error' => $message
            ],
            400
        );
    }

    /**
     * Send a JSON server error response.
     *
     * @param string|null $message
     * @return Response
     */
    public function serverError($message=null)
    {
        if ($message === null) {
            $message = 'Server error performing last action';
        }
        return $this->json(
            [
                'error' => $message,
                'ok' => false
            ],
            500
        );
    }

    /**
     * Send a JSON not found response.
     *
     * @param string|null $message
     * @return Response
     */
    public function notFound($message=null)
    {
        if ($message === null) {
            $message = 'Not found';
        }
        return $this->json(
            [
                'error' => $message,
            ],
            404
        );
    }

    /**
     * Get a GET query parameter as an integer.
     *
     * @param string $key
     * @param int $default
     * @return int|bool false
     */
    public function getIntValQueryParam($key, $default)
    {
        $v = $this->getQueryParam($key, $default);
        if ($v === null) {
            return $default;
        }
        if (!is_numeric($v)) {
            return false;
        }
        return intval($v);
    }

    /**
     * @param string $key
     * @return bool|DateTime|null
     */
    public function getYmdQueryParam($key)
    {
        $v = $this->getQueryParam($key);
        if ($v === null) {
            return null;
        }
        if (false !== $date = $this->__ymdToDateTime($v)) {
            return $date;
        }

        return null;
    }

    /**
     * Get a Ymd String
     *
     * @param string $key
     * @return bool|DateTime|null
     */
    public function getYmdStringQueryParam($key)
    {
        $date = $this->getYmdQueryParam($key);
        if ($date === null) {
            return null;
        }
        return $date->format('Y-m-d');
    }

    /**
     * Ensure a value is not empty
     *
     * @param mixed $value
     * @return boolean
     */
    private function __ymdToDateTime($value)
    {
        if (strlen(str_replace('-', '', $value)) !== 8) {
            return false;
        }
        try {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if ($date) {
                return $date;
            }
        } catch (Exception $e) {
        }
        return false;
    }
}
