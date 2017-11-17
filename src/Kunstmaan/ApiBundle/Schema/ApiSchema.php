<?php

namespace Kunstmaan\ApiBundle\Schema;

use Kunstmaan\ApiBundle\Resolver\ApiResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Schema\InternalSchemaMutationObject;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Scalar\IntType;

class ApiSchema extends AbstractSchema
{
    private $container;

    public function __construct(ContainerInterface $container, array $config=[])
    {
        $this->container = $container;
        parent::__construct($config);
    }

    public function build(SchemaConfig $config)
    {
        $names = ['user', 'group', 'role'];
        $fields = [];
        foreach ( $names as $name ) {
            $fields[$name] = $this->createSingleField($name);
            $fields[$name.'List'] = $this->createListField($name);
        }
        $config->getQuery()->addFields($fields);

        $config->setMutation(
            new InternalSchemaMutationObject([
                'name' => 'kunstmaan_api.mutation'
            ])
        );
    }

    private function createSingleField($name)
    {
        return [
            'type'      => $this->container->get('kunstmaan_api.type.'.$name),
            'args'      => [
                'id' => [
                    'type' => new IntType()
                ],
            ],
            'resolve' => ['@kunstmaan_api.resolver.'.$name, 'resolveOne'],
        ];
    }

    private function createListField($name)
    {
        return [
            'type'      => new ListType($this->container->get('kunstmaan_api.type.'.$name)),
            'args'      => ApiResolver::getLimitArgs(),
            'resolve'   => ['@kunstmaan_api.resolver.'.$name, 'resolveAll']
        ];
    }
}