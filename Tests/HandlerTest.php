<?php

namespace Lvovgeka\JsonRpcBundle\Tests;

use Lvovgeka\JsonRpcBundle\Server\Handler;
use Lvovgeka\JsonRpcBundle\Server\Mapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HandlerTest extends WebTestCase
{
    protected static $handler = null;
    protected $getJsonPayload = '{"jsonrpc": "2.0", "method": "Get", "params": {"a": 1, "b": 2}, "id": 1}';
    protected $postJsonPayload = '{"jsonrpc": "2.0", "method": "Post", "params": {"a": 1, "b": 2}, "id": 1}';

    /**
     * @return Mapper
     */
    public function getMapper()
    {
        $mapper = static::$kernel->getContainer()->get('rpc.server.mapper');

        if(!count($mapper->getMeta())) {
            $mapper->setMeta($mapper->loadPathMetadata(__DIR__ . '/Method'));
        }

        return $mapper;
    }

    /**
     * @return Handler
     */
    public function getHandler()
    {
        if(!static::$kernel) {
            static::bootKernel();
        }

        if(!self::$handler){

            $container = static::$kernel->getContainer();
            self::$handler = new Handler($container, $this->getMapper());
        }

        return self::$handler;
    }

    public function testValidGetHttpMethod()
    {

        $request = $this->getRequestWithHttpMethod(
            'GET',
            [
                'request' => $this->getJsonPayload
            ]
        );

        $handler = $this->getHandler();

        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":3,"id":1}', $response->getContent());
    }

    public function testInvalidPostHttpMethod()
    {
        $request = $this->getRequestWithHttpMethod(
            'POST',
            [
                'request' => $this->getJsonPayload
            ],
            $this->getJsonPayload
        );

        $handler = $this->getHandler();

        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Http method not available.","data":"Method: Get waiting \'GET\', and got \'POST\'"},"id":1}', $response->getContent());
    }

    public function testValidPostHttpMethod()
    {
        $request = $this->getRequestWithHttpMethod(
            'POST',
            [],
            $this->postJsonPayload
        );


        $handler = $this->getHandler();

        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":3,"id":1}', $response->getContent());
    }

    protected function getRequestWithHttpMethod($httpMethod = 'GET', $query = [],$content = null)
    {
        return new Request(
            $query
            ,
            [],
            [],
            [],
            [],
            [
                'REQUEST_METHOD' => strtoupper(strval($httpMethod))
            ],
            $content
        );
    }

    public function testHttpRequest_1()
    {

        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "subtract", "params":  { "subtrahend" : 42, "minuend" : 23 }, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_2()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "subtract", "params": { "subtrahend" : 23, "minuend" : 42 }, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_3()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_4()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_5()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "update", "params": {"a" : 1,"b" : 2,"c" : 3,"d" : 4,"e" : 5}}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_6()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_7()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_8()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": 1, "params": "bar"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_9()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"}, {"jsonrpc": "2.0", "method"]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_10()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '[]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_11()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '[1]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_12()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '[1,2,3]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_13()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [],
            '[
                {"jsonrpc": "2.0", "method": "sum", "params": {"a" : 1, "b" : 2, "c": 4}, "id": "1"},
                {"jsonrpc": "2.0", "method": "notify_hello", "params": {"a" : 7}},
                {"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend" : 42, "minuend" : 23}, "id": "2"},
                {"foo": "boo"},
                {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
                {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
             ]'
        );
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"5"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_14()
    {
        $handler = $this->getHandler();
        $request = $this->getRequestWithHttpMethod('POST', [], '{"jsonrpc": "2.0", "method": "exception_data", "id": "1"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32002,"message":"Method Error.","data":"error data"},"id":"1"}', $response->getContent(), $response->getContent());
    }
}
