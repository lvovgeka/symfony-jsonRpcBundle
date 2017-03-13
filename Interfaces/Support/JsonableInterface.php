<?php

namespace Lvovgeka\JsonRpcBundle\Interfaces\Support;

/**
 * Interface JsonableInterface
 * @package Lvovgeka\JsonRpcBundle\Interfaces\Support
 * @author lvovgeka@gmail.com
 */
interface JsonableInterface
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
