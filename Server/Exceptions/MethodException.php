<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcErrorException;

/**
 * Class MethodException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class MethodException extends RpcErrorException
{
    public function __construct($data = null)
    {
        parent::__construct('Method Error.', -32002, $data);
    }
}
