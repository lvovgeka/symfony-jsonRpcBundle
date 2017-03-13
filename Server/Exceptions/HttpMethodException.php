<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcErrorException;

/**
 * Class HttpMethodException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class HttpMethodException extends RpcErrorException
{
    public function __construct($data = null)
    {
        parent::__construct('Http method not available.', -32600, $data);
    }
}
