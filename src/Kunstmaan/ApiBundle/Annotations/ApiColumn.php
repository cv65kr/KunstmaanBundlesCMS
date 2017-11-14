<?php

namespace Kunstmaan\ApiBundle\Annotations;

use Kunstmaan\ApiBundle\Property\ApiColumnAllowedPropertiesTrait;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class ApiColumn
{
    use ApiColumnAllowedPropertiesTrait;

    public $name;

    public $type;

    public $mappedBy;

    public $description;

    private $columnClass;

    public function __construct($metadata)
    {
        if ( isset($metadata['mappedBy']) ) {
            $this->mappedBy = $metadata['mappedBy'];
        } else {
            if ( $this->isPropertyAllowed($metadata['type']) ) {
                $this->type = $metadata['type'];
                $this->columnClass = $this->getAllowedPropertyTypes()[$metadata['type']];
            }
        }
        $this->name = isset($metadata['name']) ? $metadata['name'] : null;
        $this->description = isset($metadata['description']) ? $metadata['description'] : null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMappedBy()
    {
        return $this->mappedBy;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getColumnClass()
    {
        return $this->columnClass;
    }
}