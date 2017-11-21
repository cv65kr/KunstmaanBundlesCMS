<?php

namespace Kunstmaan\ApiBundle\DependencyInjection\Definition;


use Kunstmaan\ApiBundle\Type\ApiTypeInterface;

final class ApiDefinition implements ApiDefinitionInterface
{
    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $class
     */
    private $class;

    /**
     * @var string $class
     */
    private $name;

    /**
     * @var string $serviceName
     */
    private $serviceName;

    /**
     * @var ApiDefinitionInterface $children
     */
    private $parent;

    /**
     * ApiDefinition constructor.
     * @param $type
     * @param $class
     * @param $name
     * @param string $serviceName
     * @param ApiDefinitionInterface|null $parent
     * @throws \InvalidArgumentException
     */
    public function __construct($type, $class, $name, $serviceName = null, ApiDefinitionInterface $parent = null)
    {
        $this->type = $type;
        if (false === array_key_exists($type, $this->getTypesWithServiceFormat())) {
            throw new \InvalidArgumentException(sprintf('Invalid argument %s for _construct', $type));
        }

        $this->class = $class;
        $this->name = strtolower($name);
        $this->serviceName = $serviceName;

        if (null === $this->serviceName) {
            $this->serviceName = sprintf($this->getTypesWithServiceFormat()[$this->type], $this->name);
        }

        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function getTypesWithServiceFormat()
    {
        return [
            self::TYPE_SINGLE       => 'kunstmaan_api.type.%s',
            self::TYPE_MULTI        => 'kunstmaan_api.type.list.%s',
            self::TYPE_RESOLVER     => 'kunstmaan_api.resolver.%s',
            self::TYPE_MUTATION     => 'kunstmaan_api.mutation.%s'
        ];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return ApiDefinitionInterface
     */
    public function getParent()
    {
        return $this->parent;
    }
}