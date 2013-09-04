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

namespace SimpleThings\DistributionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AliasCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getServiceIds() AS $service) {
            if (strpos($service, ".repository.") !== false && preg_match('(\.repository\.([a-z]+))', $service, $repositoryName)) {
                if (!$container->hasAlias($repositoryName[1].'.repository')) {
                    $container->setAlias($repositoryName[1].'.repository', $service);
                }
            }
        }
        $aliases = array(
            'em'                => 'doctrine.em.default_entity_manager',
            'entity.manager'    => 'doctrine.em.default_entity_manager',
            'connection'        => 'doctrine.dbal.default_connection',
            'conn'              => 'doctrine.dbal.default_connection',
            'view'              => 'fos_rest.view',
            'controller.utils'  => 'simple_things.distribution.controller_utils',
            'document.manager'  => 'doctrine_couchdb.odm.default_document_manager',
            'dm'                => 'doctrine_couchdb.odm.default_document_manager',
        );
        foreach ($aliases AS $newAlias => $serviceName) {
            if ($container->has($serviceName) && !$container->hasAlias($newAlias)) {
                $container->setAlias($newAlias, $serviceName);
            }
        }
    }
}
