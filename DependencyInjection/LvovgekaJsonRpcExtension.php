<?php

namespace Lvovgeka\JsonRpcBundle\DependencyInjection;

use Doctrine\Common\Cache\FilesystemCache;
use Lvovgeka\JsonRpcBundle\Server\Handler;
use Lvovgeka\JsonRpcBundle\Server\Mapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Lvovgeka\JsonRpcBundle\Server\Exceptions\InvalidCacheException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class LvovgekaJsonRpcExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        // Set cache
        $this->setCache($config['cache'], $container);

        // Set mapper
        $this->setMapper($config['mapping'], $container);

        // Set handler
        $this->setHandler($container);
    }

    /**
     * RPC handler
     *
     * @param ContainerBuilder $container
     */
    public function setHandler(ContainerBuilder $container)
    {
        //server
        $handler = new Definition(Handler::class, [new Reference('service_container'), new Reference('rpc.server.mapper')]);

        $handler->addMethodCall('setCache', [new Reference('rpc.server.cache')]);
        $container->setDefinition('rpc.server.handler', $handler);
    }

    /**
     * RPC mapping
     *
     * @param array            $mapping
     * @param ContainerBuilder $container
     */
    public function setMapper($mapping, ContainerBuilder $container)
    {
        $mapper = new Definition(Mapper::class, [$container->getParameter('kernel.debug')]);

        $mapper->addMethodCall('setCache', [new Reference('rpc.server.cache')]);
        $mapper->addMethodCall('setContainer', [new Reference('service_container')]);

        if (empty($mapping)) {
            $mapping = [];
            foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
                $mapping[] = '@'.$bundle.'/RpcMethod';
            }
        }

        // Add path to mapper for mapping RPC methods
        foreach ($mapping as $path) {
            $mapper->addMethodCall('addPath', [$path]);
        }

        $container->setDefinition('rpc.server.mapper', $mapper);
        $container->setParameter('rpc.server.mapping', (array)$mapping);
    }

    /**
     * RPC cache driver
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws InvalidCacheException
     */
    public function setCache(array $configs, ContainerBuilder $container)
    {

        switch ($configs['driver']) {

            case 'file':

                if (!key_exists('directory', $configs['options'])) {
                    throw new InvalidCacheException('RPC: cache file driver need directory in options sections');
                }

                if (!is_string($configs['options']['directory'])) {
                    throw new InvalidCacheException('RPC: directory options must be string');
                }

                $cache = new Definition(FilesystemCache::class, [$configs['options']['directory'], '']);

                break;

            default:

                throw new InvalidCacheException(sprintf('RPC: unknown cache driver "%s"', $configs['driver']));

        }

        $container->setParameter('rpc.server.cache', $configs);
        $container->setDefinition('rpc.server.cache', $cache);
    }
}
