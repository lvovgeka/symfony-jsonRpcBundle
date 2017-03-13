<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("Get")
 * @Rpc\HttpMethods({"GET"})
 */
class Get
{
    /**
     * @var null
     * @Rpc\Param()
     */
    protected $a;

    /**
     * @var null
     * @Rpc\Param()
     */
    protected $b;

    /**
     * @Rpc\Execute()
     * @return int
     */
	public function execute()
	{
		return (int)$this->a + (int)$this->b;
	}
}
