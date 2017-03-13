<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class InvalidParamsException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class InvalidParamsException extends RpcException
{
    protected function getDefaultMessage()
    {
        return 'Invalid params';
    }

    protected function getDefaultCode()
    {
        return -32602;
    }
}
