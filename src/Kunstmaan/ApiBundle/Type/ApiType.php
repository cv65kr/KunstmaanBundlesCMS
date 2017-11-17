<?php

namespace Kunstmaan\ApiBundle\Type;

use Doctrine\Common\Annotations\Reader;
use Kunstmaan\ApiBundle\Annotations\ApiColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

final class ApiType extends AbstractObjectType
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

            $result = [];
            if ( $field->getMappedBy() !== null ) {
                $result['type'] = new ListType(
                    $this->container->get(
                        $field->getMappedBy()
                    )
                );
                $result['resolve'] = [
                    '@kunstmaan_api.resolver.'.$this->getServiceName($field->getMappedBy()),
                    'resolveAll'
                ];
            }
            else {
                $class = $field->getColumnClass();
                $result['type'] = new $class();
            }

            $result['description'] = $field->getDescription();

            $fields[$field->getName()] = $result;
        }

        $config->addFields($fields);
    }

    private function getServiceName($name)
    {
        $name = explode('.', $name);
        return end($name);
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