<?php

namespace Lvovgeka\JsonRpcBundle\RpcMethod;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Example class
 * @Rpc\Method("Example")
 * @Rpc\HttpMethods({"GET", "HEAD", "POST"})
 * @Rpc\Cache(lifetime=3600)
 * @author lvovgeka@gmail.com
 */
class Example
{
    /**
     * @Rpc\Param()
     *
     */
    protected $param1 = 'default value';

    /**
     * @Rpc\Param()
     * @Assert\NotBlank()
     */
    protected $param2;

    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return 'result : param2 - ' . $this->param2 . '; param1 - ' . $this->param1;
    }
}
