<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;

/**
 * Class ParseErrorException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class ParseErrorException extends RpcException
{
    protected function getDefaultMessage()
    {
        return 'Parse error';
    }

    protected function getDefaultCode()
    {
        return -32700;
    }
}
