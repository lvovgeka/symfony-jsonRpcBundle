<?php

namespace Lvovgeka\JsonRpcBundle\Mapping;

/**
 * @Annotation
 * @Target("CLASS")
 * @author lvovgeka@gmail.com
 */
final class Cache
{
	/**
	 * @var integer
	 */
	public $lifetime = 0;

}
