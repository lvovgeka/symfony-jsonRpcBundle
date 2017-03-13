<?php

namespace Lvovgeka\JsonRpcBundle\Server;

use Exception;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\HttpMethodException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\RpcException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\ParseErrorException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\InternalErrorException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\InvalidParamsException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\InvalidRequestException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\MethodNotFoundException;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\MethodNotGrantedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Doctrine\Common\Cache\CacheProvider;

/**
 * Class Handler
 * @package Lvovgeka\JsonRpcBundle\Server
 * @author lvovgeka@gmail.com
 */
class Handler
{
    /**
     * Container
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    protected $container;

    /**
     * Rpc mapper
     *
     * @var \Lvovgeka\JsonRpcBundle\Server\Mapper|null
     */
    protected $mapper;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider|null
     */
    protected $cache;

    /**
     * @var callable[]
     */
    private $exceptionHandlers = [];

    /**
     * @var array
     */
    protected $httpMethodsWithQueryString = [
        'GET',
        'UNLOCK',
        'PURGE',
        'COPY'
    ];

    /**
     * Create new instance.
     *
     * @param ContainerInterface $container Instance of container
     * @param Mapper             $mapper
     */
    public function __construct(ContainerInterface $container = null, Mapper $mapper = null)
    {
        $this->container = $container;
        $this->mapper    = $mapper;
    }

    protected $httpMethod = null;
    /**
     * Handle http request
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleHttpRequest(HttpRequest $httpRequest)
    {

        if ($this->container && $this->container->has('debug.stopwatch')) {
            $stopwatch = $this->container->get('debug.stopwatch');
            $stopwatch->start('rpc');
        }

        $this->httpMethod = $httpRequest->getMethod();

        try {

            if(in_array( $this->httpMethod, $this->httpMethodsWithQueryString) && $request = $httpRequest->get('request')) {
                $payload = $request;
            }
            else {
                $payload = $httpRequest->getContent();
            }


            $payload = $this->parsePayload($payload);


            if (!is_array($payload)) {
                throw new InvalidRequestException;
            }

            if (array_keys($payload) === range(0, count($payload) - 1)) {
                $response = new BatchResponse();

                foreach ($payload as $item) {
                    $result = $this->handleFromPayload($item);

                    is_null($result) || $response->add($result);
                }
            } else {
                $response = $this->handleFromPayload($payload);
            }
        } catch (RpcException $exception) {
            $response = Response::createFromException(null, $exception);
        } catch (Exception $exception) {
            $response = $this->handleException($exception);
        }

        if (isset($stopwatch)) {
            $stopwatch->stop('rpc');
        }

        return HttpResponse::create(
            is_null($response) ? null : $response->toJson(),
            200,
            ['Content-Type' => 'application/json']
//            ['Content-Type' => 'application/json-rpc']
        );
    }

    /**
     * @param  string $payload
     *
     * @return mixed
     *
     * @throws \Lvovgeka\JsonRpcBundle\Server\Exceptions\ParseErrorException
     */
    public function parsePayload($payload)
    {
        $payload = json_decode($payload, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $payload;
        }

        throw new ParseErrorException;
    }

    /**
     * Handle from payload and return the response.
     *
     * @param  array $payload
     *
     * @return \Lvovgeka\JsonRpcBundle\Server\Response|null
     */
    public function handleFromPayload($payload)
    {
        try {
            return $this->handleRequest(
                Request::createFromPayload($payload)
                    ->setHttpMethod($this->httpMethod)
            );
        } catch (RpcException $exception) {
            return Response::createFromException(null, $exception);
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Handle request.
     *
     * @param  \Lvovgeka\JsonRpcBundle\Server\Request $request
     *
     * @return \Lvovgeka\JsonRpcBundle\Server\Response|null
     */
    public function handleRequest(Request $request)
    {

        if ($this->container && $this->container->has('debug.stopwatch')) {
            $stopwatch = $this->container->get('debug.stopwatch');
        }

        try {
            $cache = $this->getCache();

            if ($cache && $request->getId() && !$this->container->getParameter('kernel.debug')) {
                $result = $cache->fetch($request->getHash());

                if ($result) {
                    return $this->prepareResponse($request, $result);
                }
            }

            $methodName = $request->getMethod();

            $metadata = $this->mapper->loadMetadata();

            foreach ($metadata as $methodClass => $meta) {
                if ($meta['method']->value == $methodName) {

                    if(!is_null($meta['httpMethod']) && !in_array($request->getHttpMethod(), (array)$meta['httpMethod']->value)) {

                        $httpMethods = implode('|', $meta['httpMethod']->value);
                        throw new HttpMethodException("Method: {$methodName} waiting '{$httpMethods}', and got '{$request->getHttpMethod()}'");
                    }

                    if (isset($stopwatch)) {
                        $stopwatch->start('rpc.prepare');
                    }

                    $method = new $methodClass;

                    if ($method instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface) {
                        $method->setContainer($this->container);
                    }

                    $params = $this->prepareParams($meta['params'], $request->getParams());

                    $this->injectParams($method, $params);

                    $this->validateMethod($method);

                    if (!empty($meta['roles'])) {
                        $this->isGranted((array)$meta['roles']->value);
                    }

                    // Execute RPC method

                    if (isset($stopwatch)) {
                        $stopwatch->stop('rpc.prepare');
                        $stopwatch->start('rpc.execute');
                    }

                    $result = $method->{$meta['executeMethod']}();

                    if (isset($stopwatch)) {
                        $stopwatch->stop('rpc.execute');
                    }

                    if ($cache && $request->getId() && $meta['cache']) {
                        $cache->save($request->getHash(), $result, $meta['cache']->lifetime);
                    }

                    return $this->prepareResponse($request, $result);
                }
            }

            throw new MethodNotFoundException;
        } catch (RpcException $exception) {
            return $request->getId()
                ? Response::createFromException($request->getId(), $exception)
                : null;
        } catch (Exception $exception) {
            return $this->handleException($exception, $request);
        }
    }

    /**
     * @param $methodParams
     * @param $parameters
     *
     * @return array
     */
    protected function prepareParams(array $methodParams, array $parameters)
    {
        $params = [];

        $isAssocParameters = (array_keys($parameters) !== range(0, count($parameters) - 1));

        foreach ($methodParams as $name => $param) {
            if ($isAssocParameters) {
                if (array_key_exists($name, $parameters)) {
                    $params[$name] = $parameters[$name];
                }
            } else {
                if (count($parameters)) {
                    $params[$name] = array_shift($parameters);
                }
            }
        }

        return $params;
    }

    /**
     * @param       $instance
     * @param array $params
     */
    protected function injectParams($instance, array $params)
    {
        $reflection = new \ReflectionObject($instance);

        foreach ($params as $name => $value) {
            if (!$reflection->hasProperty($name)) {
                throw new InvalidParamsException;
            }

            $reflectionProperty = $reflection->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($instance, $value);
        }
    }

    /**
     * Create a response instance from the given value.
     *
     * @param  \Lvovgeka\JsonRpcBundle\Server\Request $request
     * @param  mixed                     $response
     *
     * @return \Lvovgeka\JsonRpcBundle\Server\Response|null
     */
    protected function prepareResponse(Request $request, $response)
    {
        if ($id = $request->getId()) {
            return $response instanceof Response
                ? $response->setId($id)
                : new Response($id, $response);
        }
    }

    /**
     * @param $method
     */
    protected function validateMethod($method)
    {
        if ($this->getValidator()->validate($method)->count() !== 0) {
            throw new InvalidParamsException;
        }
    }

    /**
     * Check granted access to method
     *
     * @param array $roles
     *
     * @return bool
     */
    protected function isGranted(array $roles)
    {
        $isGranted = [];

        foreach ($roles as $role) {
            $isGranted[] = $this->getAuthorizator()->isGranted($role);
        }

        if (in_array(false, $isGranted, true)) {
            throw new MethodNotGrantedException;
        }
    }

    /**
     * Get validator instance.
     *
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * Get authorization instance.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected function getAuthorizator()
    {
        return $this->getContainer()->get('security.authorization_checker');
    }

    /**
     * Register exception handler.
     *
     * @param  string   $exceptionClass
     * @param  callable $handler
     * @param  bool     $first
     *
     * @return $this
     */
    public function onException($exceptionClass, $handler, $first = false)
    {
        if ($first) {
            $this->exceptionHandlers = array_merge([$exceptionClass => $handler], $this->exceptionHandlers);
        } else {
            $this->exceptionHandlers[$exceptionClass] = $handler;
        }

        return $this;
    }

    /**
     * Handle exception.
     *
     * @param  Exception    $exception
     * @param  Request|null $request
     *
     * @return Response|null
     */
    public function handleException(Exception $exception, Request $request = null)
    {
        $handlerResult = $this->runExceptionHandlers($exception, $request);

        if ($handlerResult === false) {
            $this->container->get('logger')->error($exception->getMessage(), $exception->getTrace());
        }

        if ($request && !$request->getId()) {
            return null;
        }

        if ($handlerResult instanceof Response) {
            return $handlerResult;
        }

        return Response::createFromException(
            $request ? $request->getId() : null,
            new InternalErrorException
        );
    }

    /**
     * @param  Exception    $exception
     * @param  Request|null $request
     *
     * @return mixed|false
     */
    private function runExceptionHandlers(Exception $exception, Request $request = null)
    {
        foreach ($this->exceptionHandlers as $className => $handler) {
            if ($exception instanceof $className) {
                return $handler($exception, $request);
            }
        }

        return false;
    }

    /**
     * Get container instance
     *
     * @return ContainerInterface|null
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }

}
