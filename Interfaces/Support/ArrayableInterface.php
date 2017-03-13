<?php

namespace Lvovgeka\JsonRpcBundle\Interfaces\Support;

/**
 * Interface ArrayableInterface
 * @package Lvovgeka\JsonRpcBundle\Interfaces\Support
 * @author lvovgeka@gmail.com
 */
interface ArrayableInterface
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
