<?php

namespace Kunstmaan\ApiBundle\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;
use Youshido\GraphQL\Type\Scalar\IntType;

/**
 * Class ApiResolver
 * @package Kunstmaan\ApiBundle\Resolver
 * @based https://github.com/eliecharra/symfony-demo-api/blob/master/src/AppGraphQLBundle/Resolver/DoctrineResolver.php
 */
class ApiResolver
{
    const ALIAS = 'e';

    private $class;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ApiResolver constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct($class, EntityManagerInterface $entityManager)
    {
        $this->class            = new \ReflectionClass($class);
        $this->entityManager    = $entityManager;
    }

    public function resolveCreate($source, array $args, ResolveInfo $info)
    {
        $class = $this->class->getName();
        $model = new $class();

        dump($args);die();

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $this->findAll($source, $args, $info);
    }

    public function resolveOne($source, array $args, ResolveInfo $info)
    {
        return $this->findAll($source, $args, $info)[0];
    }

    public function resolveAll($source, array $args, ResolveInfo $info)
    {
        return $this->findAll($source, $args, $info);
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

    private function findAll($source, array $args, ResolveInfo $info)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $selectFieldList = $this->getSelectFields($info);
        $selectFieldList = sprintf('partial e.{%s}', implode($selectFieldList, ','));
        $qb->select($selectFieldList);
        $qb->from($this->class->getName(), self::ALIAS);
        $this->addJoinTypes($qb, $info);
        $this->handleSpecialArgs($qb, $args);
        foreach ($args as $key => $value) {
            $qb->andWhere($qb->expr()->eq(self::ALIAS . '.' . $key, ':' . $key))
                ->setParameter(':' . $key, $value);
        }
        return $qb->getQuery()->getResult();
    }

    private function handleSpecialArgs(QueryBuilder $qb, array &$args)
    {
        if (isset($args['__limit'])) {
            $qb->setMaxResults($args['__limit']);
            unset($args['__limit']);
        }
        if (isset($args['__offset'])) {
            $qb->setFirstResult($args['__offset']);
            unset($args['__offset']);
        }
    }

    private function addJoinTypes(QueryBuilder $qb, ResolveInfo $info)
    {
        $this->iterateOnFields(
            $info,
            function($field, $fieldType, ResolveInfo $info, $index) use ($qb)
            {
                if (!$fieldType instanceof AbstractScalarType) {
                    $fieldList = $info->getFieldASTList();
                    $selectSubFields = ['id'];
                    foreach ($fieldList as $mField) {
                        if ($mField->getName() === $field->getName() &&
                            $mField instanceof Query) {
                            foreach ($mField->getFields() as $subField) {
                                $selectSubFields[] = $subField->getName();
                            }
                        }
                    }
                    $qb->leftJoin(self::ALIAS . '.' . $field->getName(), 'r_' . $index)
                        ->addSelect('partial r_' . $index . sprintf('.{%s}', implode($selectSubFields, ',')));
                }
            }
        );
    }

    private function iterateOnFields(ResolveInfo $info, \Closure $callback)
    {
        $type = $info->getField()
            ->getType()
            ->getNamedType();
        if ($type instanceof AbstractScalarType) {
            return;
        }
        $fieldTypeList = $type->getFields();
        $fieldList = $info->getFieldASTList();
        /** @var Field $field */
        foreach ($fieldList as $index => $field) {
            $fieldType = $fieldTypeList[$field->getName()]->getType();
            $callback($field, $fieldType, $info, $index);
        }
    }

    private function getSelectFields(ResolveInfo $info)
    {
        $selectList = [
            'id'
        ];
        $this->iterateOnFields($info, function($field, $fieldType) use (&$selectList) {
            if ($fieldType instanceof AbstractScalarType) {
                $selectList[] = $field->getName();
            }
        });
        return $selectList;
    }
}