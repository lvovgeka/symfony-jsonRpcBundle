<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class InvalidRequestException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class InvalidRequestException extends RpcException
{
    protected function getDefaultMessage()
    {
        return 'Invalid Request';
    }

    protected function getDefaultCode()
    {
        return -32600;
    }
}
