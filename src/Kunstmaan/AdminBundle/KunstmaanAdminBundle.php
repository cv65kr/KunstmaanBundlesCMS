<?php

namespace Kunstmaan\AdminBundle;

use Kunstmaan\AdminBundle\DependencyInjection\Compiler\AddLogProcessorsCompilerPass;
use Kunstmaan\AdminBundle\DependencyInjection\Compiler\AdminPanelCompilerPass;
use Kunstmaan\AdminBundle\DependencyInjection\Compiler\DataCollectorAfterPass;
use Kunstmaan\AdminBundle\DependencyInjection\Compiler\DataCollectorPass;
use Kunstmaan\AdminBundle\DependencyInjection\Compiler\MenuCompilerPass;
use Kunstmaan\AdminBundle\DependencyInjection\KunstmaanAdminExtension;
use Kunstmaan\ApiBundle\DependencyInjection\Compiler\ApiCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * KunstmaanAdminBundle
 */
class KunstmaanAdminBundle extends Bundle
{

    /**
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MenuCompilerPass());
        $container->addCompilerPass(new AdminPanelCompilerPass());
        $container->addCompilerPass(new AddLogProcessorsCompilerPass());
        $container->addCompilerPass(new DataCollectorPass());
        $container->addCompilerPass(new ApiCompilerPass($this));

        $container->registerExtension(new KunstmaanAdminExtension());
    }

    /**
     * @return string The Bundle parent name it overrides or null if no parent
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
