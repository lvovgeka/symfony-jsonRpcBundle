<?php

namespace Lvovgeka\JsonRpcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RpcController
 * @package Lvovgeka\JsonRpcBundle\Controller
 * @author lvovgeka@gmail.com
 */
class RpcController extends Controller
{
    /**
     * @Route("json-rpc", name="lvovgeka_json_rpc")
     */
    public function indexAction(Request $request)
    {
        /* @var Response $response */
        $response = $this->get('rpc.server.handler')
            ->handleHttpRequest($request);

        return $response;
    }
}
