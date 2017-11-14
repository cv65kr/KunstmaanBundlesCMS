<?php

namespace Kunstmaan\ApiBundle\Schema;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

class ApiSchema extends AbstractSchema
{
    private $container;

    public function __construct(ContainerInterface $container, array $config = [])
    {
        parent::__construct($config);
        $this->container = $container;
    }

    public function build(SchemaConfig $config)
    {

        $config->getQuery()->addFields([
            'post' => [
                'type' => $this->container->get('kunstmaan_api.type.role')
            ]
        ]);
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
}