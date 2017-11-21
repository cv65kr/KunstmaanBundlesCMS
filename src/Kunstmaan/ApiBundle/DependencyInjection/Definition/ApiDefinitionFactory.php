<?php

namespace Kunstmaan\ApiBundle\DependencyInjection\Definition;

final class ApiDefinitionFactory
{
    /**
     * @param $class
     * @param $name
     * @param string|null $serviceName
     * @return ApiDefinitionInterface
     * @throws \InvalidArgumentException
     */
    public static function createSingleType($class, $name, $serviceName = null)
    {
        return new ApiDefinition(ApiDefinitionInterface::TYPE_SINGLE, $class, $name, $serviceName);
    }

    /**
     * @param $class
     * @param $name
     * @param string|null $serviceName
     * @param ApiDefinitionInterface|null $type
     * @return ApiDefinitionInterface
     * @throws \InvalidArgumentException
     */
    public static function createMultiType($class, $name, $serviceName = null, ApiDefinitionInterface $type = null)
    {
        return new ApiDefinition(ApiDefinitionInterface::TYPE_MULTI, $class, $name, $serviceName, $type);
    }

    /**
     * @param $class
     * @param $name
     * @param string|null $serviceName
     * @return ApiDefinitionInterface
     * @throws \InvalidArgumentException
     */
    public static function createResolver($class, $name, $serviceName = null)
    {
        return new ApiDefinition(ApiDefinitionInterface::TYPE_RESOLVER, $class, $name, $serviceName);
    }

    /**
     * @param $class
     * @param $name
     * @param string|null $serviceName
     * @return ApiDefinitionInterface
     * @throws \InvalidArgumentException
     */
    public static function createMutation($class, $name, $serviceName = null)
    {
        return new ApiDefinition(ApiDefinitionInterface::TYPE_MUTATION, $class, $name, $serviceName);
    }
}