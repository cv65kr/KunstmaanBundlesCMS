<?php

namespace Kunstmaan\ApiBundle\DependencyInjection\Compiler;

use Kunstmaan\ApiBundle\Annotations\ApiMeta;
use Kunstmaan\ApiBundle\Type\ApiType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\Common\Annotations\AnnotationReader;

class ApiTypeCompilerPass implements CompilerPassInterface
{
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
     */
    public function process(ContainerBuilder $container)
    {
        $apiTypes = $this->findEntityClasses($this->bundle);
        foreach ( $apiTypes as $class => $serviceId ) {
            if ( false === $container->has($serviceId) ) {
                $definition = new Definition(ApiType::class);
                $definition->setArguments([
                    $class, new Reference('service_container'), new Reference('annotation_reader')
                ]);
                $container->setDefinition($serviceId, $definition);
            }
        }
    }

    /**
     * @param BundleInterface $bundle
     * @return array
     */
    private function findEntityClasses(BundleInterface $bundle)
    {
        $directory = $bundle->getPath() . '/Entity';
        $namespace = $bundle->getNamespace() . '\Entity';

        return $this->findEntities($directory, $namespace);
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return null|object
     */
    private function readAnnotations(\ReflectionClass $reflectionClass)
    {
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getClassAnnotation($reflectionClass, ApiMeta::class);
        if ( !$annotation ) {
            return null;
        }
        $annotation->name = Container::underscore(
            $annotation->getName() === null ? $reflectionClass->getShortName() : $annotation->getName()
        );
        return $annotation;
    }

    /**
     * @param $directory
     * @param $namespace
     * @return array
     */
    private function findEntities($directory, $namespace)
    {
        $classes    = [];
        $filesystem = new Filesystem();
        if ($filesystem->exists($directory)) {
            $finder = new Finder();
            $finder->files()->in($directory)->depth(0);

            foreach ($finder as $fileInfo) {
                $baseName        = $fileInfo->getBasename('.php');
                $class           = $namespace . '\\' . $baseName;
                $reflectionClass = new \ReflectionClass($class);
                $annotation      = $this->readAnnotations($reflectionClass);
                if ( $reflectionClass->isInterface() === false && $annotation != null ) {
                    $classes[$class] = sprintf('kunstmaan_api.type.%s', $annotation->getName());
                }
            }
        }

        return $classes;
    }
}