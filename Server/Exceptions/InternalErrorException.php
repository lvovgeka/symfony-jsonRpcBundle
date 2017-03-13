<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class InternalErrorException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class InternalErrorException extends RpcException
{
    protected function getDefaultMessage()
    {
        return 'Internal error';
    }

    protected function getDefaultCode()
    {
        return -32603;
    }
}
