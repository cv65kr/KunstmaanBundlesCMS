<?php

namespace Kunstmaan\ApiBundle\Type;

use Youshido\GraphQL\Type\ListType\AbstractListType;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ApiTypeList extends AbstractListType implements ApiTypeListInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $service;

    /**
     * ApiTypeList constructor.
     * @param ContainerInterface $container
     * @param string $service
     */
    public function __construct(ContainerInterface $container, $service)
    {
        $this->container = $container;
        $this->service = $service;

        parent::__construct();
    }

    public function getItemType()
    {
        return $this->container->get(
            $this->service
        );
    }
}