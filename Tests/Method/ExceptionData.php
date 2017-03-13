<?php

namespace Lvovgeka\JsonRpcBundle\Tests\Method;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\MethodException;
use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Rpc\Method("exception_data")
 */
class ExceptionData
{
    /**
     * @Rpc\Execute()
     */
	public function execute()
	{
		throw new MethodException('error data');
	}
}
