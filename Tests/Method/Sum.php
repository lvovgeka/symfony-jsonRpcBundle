<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("sum")
 */
class Sum
{
    /**
     * @var null
     * @Rpc\Param()
     * @Assert\NotBlank()
     */
    protected $a;

    /**
     * @var null
     * @Rpc\Param()
     * @Assert\NotBlank()
     */
    protected $b;

    /**
     * @var null
     * @Rpc\Param()
     * @Assert\NotBlank()
     */
    protected $c;

    /**
     * @Rpc\Execute()
     */
	public function execute()
	{
		return $this->a + $this->b + $this->c;
	}
}
