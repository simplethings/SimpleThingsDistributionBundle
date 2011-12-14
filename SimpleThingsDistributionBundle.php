<?php

/**
 * SimpleThings TransactionalBundle
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eberlei@simplethings.de so I can send you a copy immediately.
 */

namespace SimpleThings\DistributionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use SimpleThings\DistributionBundle\DependencyInjection\AliasCompilerPass;
use SimpleThings\DistributionBundle\DependencyInjection\RepositoryCompilerPass;

class SimpleThingsDistributionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AliasCompilerPass());
        $container->addCompilerPass(new RepositoryCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
