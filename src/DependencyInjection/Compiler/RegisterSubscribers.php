<?php

namespace SimpleBus\SymfonyBridge\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterSubscribers implements CompilerPassInterface
{
    use CollectServices;

    private $serviceId;
    private $tag;
    private $keyAttribute;

    /**
     * @param string  $serviceId            The service id of the MessageSubscriberCollection
     * @param string  $tag                  The tag name of message subscriber services
     * @param string  $keyAttribute         The name of the tag attribute that contains the name of the subscriber
     */
    public function __construct($serviceId, $tag, $keyAttribute)
    {
        $this->serviceId = $serviceId;
        $this->tag = $tag;
        $this->keyAttribute = $keyAttribute;
    }

    /**
     * Search for message subscriber services and provide them as a constructor argument to the message subscriber
     * collection service.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->serviceId)) {
            return;
        }

        $definition = $container->findDefinition($this->serviceId);

        $handlers = array();

        $this->collectServiceIds(
            $container,
            $this->tag,
            $this->keyAttribute,
            function ($key, $serviceId, array $tagAttributes) use (&$handlers) {
                if (isset($tagAttributes['method'])) {
                    $callable = [$serviceId, $tagAttributes['method']];
                } else {
                    $callable = $serviceId;
                }

                $handlers[$key][] = $callable;
            }
        );

        $definition->replaceArgument(0, $handlers);
    }
}
