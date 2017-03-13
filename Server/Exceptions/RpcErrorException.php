<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use InvalidArgumentException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class RpcErrorException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class RpcErrorException extends RpcException
{
    public function __construct($message = '', $code = 0, $data = null)
    {
        $code = $code ?: $this->getDefaultCode();

        if ($code >= -32000 && $code <= -32099)
        {
            throw new InvalidArgumentException("Code out of range");
        }

        parent::__construct($message, $code, $data);
    }

    protected function getDefaultMessage()
    {
        return 'Internal error';
    }

    protected function getDefaultCode()
    {
        return -32000;
    }
}
