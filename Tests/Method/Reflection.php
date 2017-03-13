<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("test.Reflection")
 */
class Reflection
{
    /**
     * @var null
     * @Rpc\Param()
     */
    protected $a = null;

    /**
     * @var null
     * @Rpc\Param()
     */
    protected $b = null;

    /**
     * @Rpc\Execute()
     */
	public function execute()
	{
		return $this->a + $this->b;
	}
}
