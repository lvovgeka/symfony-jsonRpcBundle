<?php

namespace Lvovgeka\JsonRpcBundle\Server;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\InvalidMappingException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RPC Mapper
 *
 * Service for found RPC methods in bundles and mapping methods metadata
 */

/**
 * Class Mapper
 * Service for found RPC methods in bundles and mapping methods metadata
 * @package Lvovgeka\JsonRpcBundle\Server
 */
class Mapper implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @var array Paths for mapping
     */
    protected $paths;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Create instance of Mapper
     *
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->reader = new AnnotationReader(new DocParser());
        $this->debug  = $debug;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Set $meta
     *
     * @param $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * Get $meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set cache
     *
     * @param CacheProvider $cache Cache provider
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get cache
     *
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get annotation reader
     *
     * @return AnnotationReader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Add path for mapping rpc methods
     *
     * @param string $path
     *
     * @return void
     */
    public function addPath($path)
    {
        if ($path[0] === '@' & !empty($this->container)) {
            try{
                $path = $this->container->get('kernel')->locateResource($path);
            }
            catch(\Exception $e){}
        }

        if (is_dir($path)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Load all metadata from mapping path
     *
     * @return array
     */
    public function loadMetadata()
    {

        if (!empty($this->meta)) {
            return $this->meta;
        }

        if ($this->cache && !$this->debug && $meta = $this->cache->fetch('rpc.meta')) {

            $this->meta = $meta;

            return $meta;
        }

        if ($this->container && $this->container->has('debug.stopwatch')) {
            $stopwatch = $this->container->get('debug.stopwatch');
            $stopwatch->start('rpc.mapping');
        }

        $meta = [];

        foreach ($this->paths as $path) {
            $meta += $this->loadPathMetadata($path);
        }

        if ($this->cache) {
            $this->cache->save('rpc.meta', $meta);
        }


        $this->meta = $meta;

        if (isset($stopwatch)) {
            $stopwatch->stop('rpc.mapping');
        }

        return $meta;
    }

    /**
     * Load mapping metadata for all find PRC methods in path
     *
     * @param string $path Mapping path
     *
     * @return array
     */
    public function loadPathMetadata($path)
    {
        if (!empty($this->meta)) {
            return $this->meta;
        }

        $meta = [];

        if (is_dir($path)) {

            $dir = new \DirectoryIterator($path);

            foreach ($dir as $file) {

                if ($file->isFile()) {

                    if ($metas = $this->loadFileMetadata($file->getRealPath())) {

                        foreach ($metas as $values) {

                            $meta[$values['class']] = $values;

                        }

                    }

                }

                if ($file->isDir() && ! $file->isDot()) {
                    $meta += $this->loadPathMetadata($file->getRealPath());
                }

            }

        }

        $this->setMeta($meta);

        return $meta;
    }

    /**
     * load RPC method metadata for object
     *
     * @param object $object
     *
     * @return array|null
     */
    public function loadObjectMetadata($object)
    {
        $reflection = new \ReflectionObject($object);

        return $this->loadClassMetadata($reflection->getName());
    }

    /**
     * Load RPC method metadata from class
     *
     * @param string $class Class
     *
     * @return array|null
     * @throws InvalidMappingException
     */
    public function loadClassMetadata($class)
    {

        if (array_key_exists($class, $this->meta)) {
            return $this->meta[$class];
        }

        $meta = null;

        if (class_exists($class)) {

            $reflectionClass = new \ReflectionClass($class);

            if ($method = $this->reader->getClassAnnotation($reflectionClass, 'Lvovgeka\JsonRpcBundle\Mapping\Method')) {

                $meta = [];

                if (empty($method->value)) {
                    throw new InvalidMappingException(sprintf('RPC: Method annotation must have name in class "%s"', $class));
                }

                $meta['method'] = $method;
                $meta['class']  = $class;
                $meta['file']   = $reflectionClass->getFileName();

                // HttpMethod
                $meta['httpMethod'] = $this->reader->getClassAnnotation($reflectionClass, 'Lvovgeka\JsonRpcBundle\Mapping\HttpMethods');

                // Cache
                $meta['cache'] = $this->reader->getClassAnnotation($reflectionClass, 'Lvovgeka\JsonRpcBundle\Mapping\Cache');

                // Roles
                $meta['roles'] = $this->reader->getClassAnnotation($reflectionClass, 'Lvovgeka\JsonRpcBundle\Mapping\Roles');

                // Method execute
                if (empty($method->value)) {
                    throw new InvalidMappingException(sprintf('RPC: Method annotation must have name in class "%s"', $class));
                }

                $meta['executeMethod'] = null;

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {

                    if ($paramMeta = $this->reader->getMethodAnnotation($reflectionMethod, 'Lvovgeka\JsonRpcBundle\Mapping\Execute')) {

                        $meta['executeMethod'] = $reflectionMethod->name;

                    }

                }

                if (empty($meta['executeMethod'])) {
                    throw new InvalidMappingException(sprintf('RPC: Method need have Execute annotation in class "%s"', $class));
                }

                // Params
                $meta['params'] = [];

                foreach ($reflectionClass->getProperties() as $reflectionProperty) {

                    if ($paramMeta = $this->reader->getPropertyAnnotation($reflectionProperty, 'Lvovgeka\JsonRpcBundle\Mapping\Param')) {

                        $meta['params'][$reflectionProperty->name] = $paramMeta;

                    }
                }

            }

        }

        return $meta;
    }

    /**
     * Load RPC method metadata from file
     *
     * @param string $file Rpc method file
     *
     * @return array|null
     */
    public function loadFileMetadata($file)
    {
        if (file_exists($file)) {

            $meta = [];

            $classes = get_declared_classes();

            include_once $file;

            foreach (array_diff(get_declared_classes(), $classes) as $class) {
                if ($data = $this->loadClassMetadata($class)) {
                    $meta[] = $data;
                }
            }

            return $meta;
        }

        return null;
    }
}
