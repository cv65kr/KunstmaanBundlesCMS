<?php

namespace Kunstmaan\ApiBundle\Schema;

use Kunstmaan\ApiBundle\Event\SchemaTypeEvent;
use Kunstmaan\ApiBundle\Type\ApiResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\Scalar\IntType;

final class ApiSchema extends AbstractSchema
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * ApiSchema constructor.
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     * @param array $config
     */
    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        array $config = []
    )
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($config);
    }

    public function build(SchemaConfig $config)
    {
        //@TODO dynamic build schema
        $names = ['group', 'role', 'node', 'node_translation'];
        $fields = [];
        foreach ( $names as $name ) {
            $fields[$name] = $this->createSingleField($name);
            $fields[$name.'List'] = $this->createListField($name);
        }
        $this->eventDispatcher->dispatch(SchemaTypeEvent::NAME, new SchemaTypeEvent($fields));
        $config->getQuery()->addFields($fields);

        //@TODO Mutations
    }

    private function createSingleField($name)
    {
        return [
            'type'      => $this->container->get(sprintf('kunstmaan_api.type.%s', $name)),
            'args'      => [
                'id' => [
                    'type' => new IntType()
                ],
            ],
            'resolve' => [sprintf('@kunstmaan_api.resolver.%s', $name), 'resolveOne'],
        ];
    }

    private function createListField($name)
    {
        return [
            'type'      => $this->container->get(sprintf('kunstmaan_api.type.list.%s', $name)),
            'args'      => ApiResolver::getLimitArgs(),
            'resolve'   => [sprintf('@kunstmaan_api.resolver.%s', $name), 'resolveAll']
        ];
    }
}