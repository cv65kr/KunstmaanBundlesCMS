<?php

namespace Kunstmaan\ApiBundle\DependencyInjection\Definition;

interface ApiDefinitionInterface
{
    const TYPE_SINGLE = 0;

    const TYPE_MULTI = 1;

    const TYPE_RESOLVER = 2;

    const TYPE_MUTATION = 3;

    public function getTypesWithServiceFormat();
}