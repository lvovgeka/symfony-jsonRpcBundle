<?php

namespace Lvovgeka\JsonRpcBundle\Tests;

use Lvovgeka\JsonRpcBundle\Server\Exceptions\InvalidRequestException;
use Lvovgeka\JsonRpcBundle\Server\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonRequestTest extends WebTestCase
{
    /**
     * testNullRequest
     */
	public function testNullRequest()
	{
		try
		{
			Request::createFromPayload(
			    [
					'jsonrpc' => '2.0',
					'method'  => null,
					'params'  => null,
					'id'      => null,
				]
			);
		}
		catch (InvalidRequestException $exception)
		{
            $this->assertEquals($exception->getMessage(), $this->getInvalidRequestMessage());
		}
		catch (\Exception $exception)
		{
			$this->assertEquals(true, false);
		}
	}


    /**
     * testParamsStringRequest
     */
	public function testParamsStringRequest()
	{
		try
		{
			 Request::createFromPayload(
                 [
					'jsonrpc' => '2.0',
					'method'  => 'test',
					'params'  =>  'string',
					'id'      => 1,
				]
			);

		}
		catch (InvalidRequestException $exception)
		{
            $this->assertEquals($exception->getMessage(), $this->getInvalidRequestMessage());
		}
		catch (\Exception $exception)
		{
			$this->assertEquals(true, true);
		}
	}

	/**
	 * testParamsArrayRequest
	 */
	public function testParamsArrayRequest()
	{
		try
		{
		    $arr = [1, 2, 3];
			$request = Request::createFromPayload(
				[
					'jsonrpc' => '2.0',
					'method'  => 'string',
					'params'  => $arr,
					'id'      => 1,
				]
			);

			$this->assertEquals($arr, $request->getParams());
		}
		catch (InvalidRequestException $exception)
		{
            $this->assertEquals($exception->getMessage(), $this->getInvalidRequestMessage());
		}
		catch (\Exception $exception)
		{
			$this->assertEquals(true, true);
		}
	}


    protected function getInvalidRequestMessage()
    {
        $method = (new \ReflectionClass(new InvalidRequestException))->getMethod('getDefaultMessage');
        $method->setAccessible(true);

        return $method->invoke(new InvalidRequestException(), 'getDefaultMessage');
    }
}
