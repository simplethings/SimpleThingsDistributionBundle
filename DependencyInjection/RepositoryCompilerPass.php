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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Automatically register services named "bundle_alias".repository."entity." for
 * all Doctrine EntityMannager entities.
 */
class RepositoryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getServiceIds() AS $service) {
            if (strpos($service, "_entity_manager") !== false) {
                $manager = $container->get($service);
                if ($manager instanceof \Doctrine\ORM\EntityManager) {
                    $entities = $container->get($service)->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

                    foreach ($entities as $entity) {
                        $this->registerEntityRepository($service, $entity, $container);
                    }
                }
            }
        }
    }

    /**
     * Registers the entity repository
     */
    private function registerEntityRepository($service, $entity, $container)
    {
        $namespace = substr($entity, 0, strrpos($entity, '\\')-1);
        $shortname = substr($entity, strrpos($entity, '\\')+1);
        if (preg_match('(([a-zA-Z0-9\\\\]+Bundle))', $namespace, $match)) {
            $alias = Container::underscore(str_replace(array("\\", "Bundle"), "", $match[1]));

            $class = 'Doctrine\ORM\EntityRepository';
            $def = new Definition($class);
            $def->setFactoryService($service);
            $def->setFactoryMethod('getRepository');
            $def->addArgument($entity);

            $repositoryServiceId = $alias.'.repository.'.strtolower($shortname);
            if (!$container->hasDefinition($repositoryServiceId)) {
                $container->setDefinition($repositoryServiceId, $def);
            }
        }
    }
}

