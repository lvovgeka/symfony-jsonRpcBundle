<?php

namespace Lvovgeka\JsonRpcBundle\Server\Exceptions;

use Exception;

/**
 * Class RpcDumpException
 * @package Lvovgeka\JsonRpcBundle\Server\Exceptions
 * @author lvovgeka@gmail.com
 */
class RpcDumpException extends Exception
{
    protected $data;

    public function __construct($data = null)
    {
        $this->data = $data;

        parent::__construct();
    }

    public function getData()
    {
        return $this->data;
    }
}
