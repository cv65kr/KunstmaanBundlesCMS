<?php

namespace Kunstmaan\ApiBundle\Type;

use Doctrine\Common\Annotations\Reader;
use Kunstmaan\ApiBundle\Annotations\ApiColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class ApiType extends AbstractObjectType
{
    private $class;

    private $container;

    private $annotationReader;

    public function __construct($class, ContainerInterface $container, Reader $annotationReader, array $config=[])
    {
        $this->class            = new \ReflectionClass($class);
        $this->container        = $container;
        $this->annotationReader = $annotationReader;

        parent::__construct($config);
    }

    public function build($config)
    {
        $fields = [];
        foreach ( $this->findAllProperties() as $field ) {
            if ( $field->getMappedBy() !== null ) {
                $type = $this->container->get($field->getMappedBy());
            } else {
                $class = $field->getColumnClass();
                $type = new $class();
            }

            $fields[$field->getName()] = [
                'type' => $type,
                'description' => $field->getDescription()
            ];
        }

        $config->addFields($fields);
    }

    private function findAllProperties()
    {
        $properties = [];
        foreach ( $this->class->getProperties() as $property ) {
            $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($property, ApiColumn::class);
            if ( !$propertyAnnotation ) {
                continue;
            }
            $propertyAnnotation->name = $propertyAnnotation->getName() == null ? $property->getName() : $propertyAnnotation->getName();
            $properties[] = $propertyAnnotation;
        }
        return $properties;
    }
}