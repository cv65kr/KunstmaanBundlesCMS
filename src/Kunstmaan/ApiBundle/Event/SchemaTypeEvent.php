<?php

namespace Kunstmaan\ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class SchemaTypeEvent extends Event
{
    const NAME = 'api.type';

    protected $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getfields()
    {
        return $this->fields;
    }
}