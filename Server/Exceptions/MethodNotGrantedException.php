<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class MethodNotGrantedException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class MethodNotGrantedException extends RpcException
{
    protected function getDefaultMessage()
    {
        return "Method not granted";
    }

    protected function getDefaultCode()
    {
        return -32001;
    }
}
