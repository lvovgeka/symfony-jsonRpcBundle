<?php

namespace Lvovgeka\JsonRpcBundle\Server;

use Exception;
use Lvovgeka\JsonRpcBundle\Interfaces\Support\JsonableInterface;
use Lvovgeka\JsonRpcBundle\Interfaces\Support\ArrayableInterface;

/**
 * Class Response
 * @package Lvovgeka\JsonRpcBundle\Server
 * @author lvovgeka@gmail.com
 */
class Response implements JsonableInterface, ArrayableInterface
{
    /**
     * @var int|null|string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var bool
     */
    protected $error;

    /**
     * @param string|int|null $id
     * @param mixed           $result
     * @param bool            $error
     */
    public function __construct($id, $result, $error = false)
    {
        $this->id = $id;
        $this->result = $result;
        $this->error = (bool) $error;
    }

    /**
     * Create response from exception.
     *
     * @param  string|int|null $id
     * @param  \Exception      $exception
     *
     * @return \Lvovgeka\JsonRpcBundle\Server\Response
     */
    public static function createFromException($id, Exception $exception)
    {
        $data = method_exists($exception, 'getData') ? $exception->getData() : null;

        return static::createErrorResponse($id, $exception->getMessage(), $exception->getCode(), $data);
    }

    /**
     * Create error response.
     *
     * @param  mixed  $id
     * @param  string $message
     * @param  int    $code
     * @param  mixed  $data
     *
     * @return \Lvovgeka\JsonRpcBundle\Server\Response
     */
    public static function createErrorResponse($id, $message, $code = 0, $data = null)
    {
        $error = [
            'code'    => $code ?: -32603,
            'message' => (string) $message,
        ];

        return new static($id, array_merge($error, $data ? ['data' => $data] : []), true);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode((object) $this->toArray(), $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = ['jsonrpc' => '2.0'];

        if ($this->error)
        {
            $result['error'] = $this->result;
        }
        else
        {
            $result['result'] = $this->result;
        }

        $result['id'] = $this->id;

        return $result;
    }

    /**
     * Set response id.
     *
     * @param  $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
