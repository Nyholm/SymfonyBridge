<?php

namespace SimpleBus\SymfonyBridge;

use SimpleBus\SymfonyBridge\DependencyInjection\Compiler\AddMiddlewareTags;
use SimpleBus\SymfonyBridge\DependencyInjection\Compiler\CompilerPassUtil;
use SimpleBus\SymfonyBridge\DependencyInjection\DoctrineOrmBridgeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineOrmBridgeBundle extends Bundle
{
    use RequiresOtherBundles;

    private $configurationAlias;

    public function __construct($configurationAlias = 'doctrine_orm_bridge')
    {
        $this->configurationAlias = $configurationAlias;
    }

    public function getContainerExtension()
    {
        return new DoctrineOrmBridgeExtension($this->configurationAlias);
    }

    public function build(ContainerBuilder $container)
    {
        $this->checkRequirements(array('SimpleBusCommandBusBundle', 'SimpleBusEventBusBundle'), $container);

        $compilerPass = new AddMiddlewareTags(
            'simple_bus.doctrine_orm_bridge.wraps_next_command_in_transaction',
            ['command'],
            100
        );
        CompilerPassUtil::prependBeforeOptimizationPass($container, $compilerPass);
    }
}
