<?php

namespace Kunstmaan\ApiBundle\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class ApiMeta
{
    public $name;

    public function __construct($metadata)
    {
        $this->name = isset($metadata['name']) ? $metadata['name'] : null;
    }

    public function getName()
    {
        return $this->name;
    }
}