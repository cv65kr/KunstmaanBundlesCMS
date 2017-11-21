<?php

namespace Kunstmaan\NodeBundle;

use Kunstmaan\ApiBundle\DependencyInjection\CompilerPass\ApiCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * KunstmaanNodeBundle
 */
class KunstmaanNodeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ApiCompilerPass($this));
    }
}
