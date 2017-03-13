<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("subtract")
 */
class Subtract
{
    /**
     * @var null
     * @Rpc\Param()
     */
    protected $subtrahend;

    /**
     * @var null
     * @Rpc\Param()
     */
    protected $minuend;

    /**
     * @Rpc\Execute()
     */
	public function execute()
	{
		return $this->subtrahend - $this->minuend;
	}
}
