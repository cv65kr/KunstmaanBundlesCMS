<?php

namespace Kunstmaan\ApiBundle\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

// https://github.com/eliecharra/symfony-demo-api/blob/master/src/AppGraphQLBundle/Resolver/DoctrineResolver.php
abstract class AbstractApiResolver implements ApiResolverInterface
{
    /**
     * @var \ReflectionClass
     */
    protected $class;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ApiResolver constructor.
     * @param string $class
     * @param EntityManagerInterface $entityManager
     * @throws \ReflectionException
     */
    public function __construct($class, EntityManagerInterface $entityManager)
    {
        $this->class            = new \ReflectionClass($class);
        $this->entityManager    = $entityManager;
    }

    protected function findAll($source, array $args, ResolveInfo $info)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $selectFieldList = $this->getSelectFields($info);
        $selectFieldList = sprintf('partial e.{%s}', implode($selectFieldList, ','));
        $qb->select($selectFieldList);
        $qb->from($this->class->getName(), self::ALIAS);
        $this->addJoinTypes($qb, $info);
        $this->handleSpecialArgs($qb, $args);
        foreach ($args as $key => $value) {
            $qb->andWhere($qb->expr()->eq(self::ALIAS . '.' . $key, ':' . $key));
            $qb->setParameter(':' . $key, $value);
        }

        $pagination = new Paginator($qb, true);
        $results = new ArrayCollection();
        foreach($pagination as $item) {
            $results->add($item);
        }
        return $results->toArray();
    }

    protected function handleSpecialArgs(QueryBuilder $qb, array &$args)
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

    protected function addJoinTypes(QueryBuilder $qb, ResolveInfo $info)
    {
        $this->iterateOnFields($info, function($field, $fieldType, ResolveInfo $info, $index) use ($qb) {
            if (!$fieldType instanceof AbstractScalarType) {
                $fieldList = $info->getFieldASTList();
                $selectSubFields = ['id'];
                foreach ($fieldList as $mField) {
                    if (
                        $mField instanceof Query &&
                        $mField->getName() === $field->getName()
                    ) {
                        foreach ($mField->getFields() as $subField) {
                            $selectSubFields[] = $subField->getName();
                        }
                    }
                }
                $selectSubFields = array_unique($selectSubFields);
                $qb->leftJoin(self::ALIAS . '.' . $field->getName(), 'r_' . $index);
                $qb->addSelect('partial r_' . $index . sprintf('.{%s}', implode($selectSubFields, ',')));
            }
        });
    }

    protected function iterateOnFields(ResolveInfo $info, \Closure $callback)
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

    protected function getSelectFields(ResolveInfo $info)
    {
        $selectList = [
            'id'
        ];
        $this->iterateOnFields($info, function($field, $fieldType) use (&$selectList) {
            if ($fieldType instanceof AbstractScalarType) {
                $selectList[] = $field->getName();
            }
        });
        return array_unique($selectList);
    }
}