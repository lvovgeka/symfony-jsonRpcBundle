<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("get_data")
 */
class GetData
{
    /**
     * @Rpc\Execute()
     */
	public function execute()
	{
		return ["hello", 5];
	}
}
