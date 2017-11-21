<?php

namespace Kunstmaan\ApiBundle\DependencyInjection\CompilerPass;

use Kunstmaan\ApiBundle\DependencyInjection\Definition\ApiDefinitionFactory;
use Kunstmaan\ApiBundle\DependencyInjection\Definition\ApiDefinitionInterface;
use Kunstmaan\ApiBundle\Helper\ApiAnnotationFinder;
use Kunstmaan\ApiBundle\Type\ApiMutation;
use Kunstmaan\ApiBundle\Type\ApiResolver;
use Kunstmaan\ApiBundle\Type\ApiType;
use Kunstmaan\ApiBundle\Type\ApiTypeList;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class ApiCompilerPass implements CompilerPassInterface
{
    /**
     * @var BundleInterface
     */
    private $bundle;

    /**
     * ApiTypeCompilerPass constructor.
     * @param BundleInterface $bundle
     */
    public function __construct(BundleInterface $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $services = $this->generateServices();
        usort($services, function(ApiDefinitionInterface $a, ApiDefinitionInterface $b){
            return $a->getType() - $b->getType();
        });

        foreach($services as $service) {
            switch($service->getType())
            {
                case ApiDefinitionInterface::TYPE_SINGLE:
                    $definition = new Definition(ApiType::class);
                    $definition->setArguments([
                        $service->getClass(),
                        new Reference('service_container'),
                        new Reference('annotation_reader')
                    ]);
                    $container->setDefinition($service->getServiceName(), $definition);
                    break;

                case ApiDefinitionInterface::TYPE_MULTI:
                    $definition = new Definition(ApiTypeList::class);
                    $definition->setArguments([
                        new Reference('service_container'), $service->getParent()->getServiceName()
                    ]);
                    $container->setDefinition($service->getServiceName(), $definition);
                    break;

                case ApiDefinitionInterface::TYPE_RESOLVER:
                    $definition = new Definition(ApiResolver::class);
                    $definition->setArguments([
                        $service->getClass(),
                        new Reference('doctrine.orm.entity_manager')
                    ]);
                    $container->setDefinition($service->getServiceName(), $definition);
                    break;

                // @TODO mutation
                case ApiDefinitionInterface::TYPE_MUTATION:
                    $definition = new Definition(ApiMutation::class);
                    $container->setDefinition($service->getServiceName(), $definition);
                    break;
            }
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    private function generateServices()
    {
        $services = [];
        $apiAnnotationFinder = new ApiAnnotationFinder();
        foreach($apiAnnotationFinder->findEntityClasses($this->bundle) as $class => $name) {
            $services[] = ($parentRow = ApiDefinitionFactory::createSingleType($class, $name));
            $services[] = ApiDefinitionFactory::createMultiType($class, $name, null, $parentRow);
            $services[] = ApiDefinitionFactory::createResolver($class, $name);
            $services[] = ApiDefinitionFactory::createMutation($class, $name);
        }
        return $services;
    }
}