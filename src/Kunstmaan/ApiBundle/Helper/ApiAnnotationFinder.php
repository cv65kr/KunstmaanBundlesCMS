<?php

namespace Kunstmaan\ApiBundle\Helper;

use Doctrine\Common\Annotations\AnnotationReader;
use Kunstmaan\ApiBundle\Annotations\ApiMeta;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class ApiAnnotationFinder
{
    /**
     * @param $directory
     * @param $namespace
     * @return array
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function findEntities($directory, $namespace)
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
                if ( $annotation !== null &&
                    $reflectionClass->isInterface() === false
                ) {
                    $classes[$class] = $annotation->getName();
                }
            }
        }
        return $classes;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return null|object
     */
    public function readAnnotations(\ReflectionClass $reflectionClass)
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
     * @param BundleInterface $bundle
     * @return array
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function findEntityClasses(BundleInterface $bundle)
    {
        $directory = $bundle->getPath() . '/Entity';
        $namespace = $bundle->getNamespace() . '\Entity';
        return $this->findEntities($directory, $namespace);
    }
}