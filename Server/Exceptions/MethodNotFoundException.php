<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class MethodNotFoundException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class MethodNotFoundException extends RpcException
{
    protected function getDefaultMessage()
    {
        return "Method not found";
    }

    protected function getDefaultCode()
    {
        return -32601;
    }
}
