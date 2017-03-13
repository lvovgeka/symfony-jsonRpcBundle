<?php

namespace Lvovgeka\JsonRpcBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class DebugRpcMethodsCommand
 * @package Lvovgeka\RpcBundle\Command
 * @author lvovgeka@gmail.com
 */
class DebugRpcMethodsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('debug:rpc:methods')
            ->setDescription('Output all rpc method.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getContainer();

        $mapper = $container->get('rpc.server.mapper');

        $i = 0;
        $headers = ['#','method', 'class', 'params'];
        $tableData = [];
        foreach ($mapper->loadMetadata() as $methodClass => $meta) {
            if($i > 0) $tableData[] = new TableSeparator();

            $tableData[] = [
                $i++,
                $meta['method']->value,
                $meta['class'],
                ( implode(' ' . PHP_EOL, array_keys($meta['params']) ) ?: '' )
            ];
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($tableData);
        $table->render();
    }

}
