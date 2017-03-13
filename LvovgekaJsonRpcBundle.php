<?php

namespace Lvovgeka\JsonRpcBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lvovgeka\JsonRpcBundle\DependencyInjection\Compiler\RpcMethodsCompiler;

class LvovgekaJsonRpcBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RpcMethodsCompiler());
    }
}
