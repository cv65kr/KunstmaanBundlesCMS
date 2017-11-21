<?php

namespace Kunstmaan\ApiBundle\Type;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\PersistentCollection;
use Kunstmaan\ApiBundle\Annotations\ApiColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\IdType;

final class ApiType extends AbstractObjectType implements ApiTypeInterface
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * ApiType constructor.
     * @param array $class
     * @param ContainerInterface $container
     * @param Reader $annotationReader
     * @param array $config
     * @throws \ReflectionException
     */
    public function __construct(
        $class,
        ContainerInterface $container,
        Reader $annotationReader,
        array $config = []
    )
    {
        $this->class            = new \ReflectionClass($class);
        $this->container        = $container;
        $this->annotationReader = $annotationReader;

        parent::__construct($config);
    }

    /**
     * @param \Youshido\GraphQL\Config\Object\ObjectTypeConfig $config
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function build($config)
    {
        $fields = [
            'id' => ['type' => new IdType()]
        ];
        foreach ( $this->findAllProperties() as $field ) {

            $result = [];
            if (null !== $field->getMappedBy()) {
                $result['type'] = $this->container->get(sprintf('kunstmaan_api.type.list.%s', $field->getMappedBy()));
            } else {
                $class = $field->getColumnClass();
                $result['type'] = new $class();
            }

            $result['description'] = $field->getDescription();
            $fields[$field->getName()] = $result;
        }
        $config->addFields($fields);
    }

    /**
     * @return array
     */
    private function findAllProperties()
    {
        $properties = [];
        foreach ( $this->class->getProperties() as $property ) {
            $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($property, ApiColumn::class);
            if (!$propertyAnnotation) {
                continue;
            }
            $propertyAnnotation->name = $propertyAnnotation->getName() === null ?
                $property->getName() :
                $propertyAnnotation->getName()
            ;

            $properties[] = $propertyAnnotation;
        }
        return $properties;
    }
}