<?php

namespace Kunstmaan\ApiBundle\Type;

use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\IntType;

final class ApiResolver extends AbstractApiResolver
{
    public function resolveCreate($source, array $args, ResolveInfo $info)
    {
        $class = $this->class->getName();
        $model = new $class();
        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return parent::findAll($source, $args, $info);
    }

    public function resolveOne($source, array $args, ResolveInfo $info)
    {
        return parent::findAll($source, $args, $info)[0];
    }

    public function resolveAll($source, array $args, ResolveInfo $info)
    {
        return parent::findAll($source, $args, $info);
    }

    public static function getLimitArgs(array $args = [])
    {
        return array_merge(
            $args,
            [
                '__limit' => [
                    'type' => new IntType()
                ],
                '__offset' => [
                    'type' => new IntType()
                ]
            ]
        );
    }

}