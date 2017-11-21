<?php

namespace Kunstmaan\ApiBundle\Annotations;

use Youshido\GraphQL\Type\Scalar\BooleanType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\Scalar\DateTimeTzType;
use Youshido\GraphQL\Type\Scalar\DateType;
use Youshido\GraphQL\Type\Scalar\FloatType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Scalar\TimestampType;

trait ApiColumnAllowedPropertiesTrait
{
    public function isPropertyAllowed($type)
    {
        if ( false === array_key_exists($type, $this->getAllowedPropertyTypes()) ) {
            throw new \InvalidArgumentException('Invalid type for API');
        }
        return true;
    }

    public function getAllowedPropertyTypes()
    {
        return [
            'boolean'       => BooleanType::class,
            'date'          => DateType::class,
            'dateTime'      => DateTimeType::class,
            'dateTimeTz'    => DateTimeTzType::class,
            'float'         => FloatType::class,
            'id'            => IdType::class,
            'int'           => IntType::class,
            'string'        => StringType::class,
            'timestamp'     => TimestampType::class
        ];
    }
}