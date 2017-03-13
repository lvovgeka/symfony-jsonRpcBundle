<?php

namespace Lvovgeka\JsonRpcBundle\Server;

use Lvovgeka\JsonRpcBundle\Interfaces\Support\JsonableInterface;
use Lvovgeka\JsonRpcBundle\Interfaces\Support\ArrayableInterface;

/**
 * Class BatchResponse
 * @package Lvovgeka\JsonRpcBundle\Server
 * @author lvovgeka@gmail.com
 */
class BatchResponse implements JsonableInterface, ArrayableInterface
{
    /**
     * @var array
     */
    protected $responses = [];

    /**
     * @param  \Lvovgeka\JsonRpcBundle\Interfaces\Support\ArrayableInterface $response
     */
    public function add(ArrayableInterface $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $array = $this->toArray();

        return json_encode(count($array) ? $array : null, $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(
            function (ArrayableInterface $response) {
                return (object) $response->toArray();
            },
            $this->responses
        );
    }
}
