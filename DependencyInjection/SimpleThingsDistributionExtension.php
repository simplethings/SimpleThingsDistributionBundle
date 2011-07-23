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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use SimpleThings\TransactionalBundle\Transactions\TransactionDefinition;

class SimpleThingsDistributionExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $builder
     */
    public function load(array $configs, ContainerBuilder $builder)
    {
        $loader = new XmlFileLoader($builder, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = array();
        foreach ($configs AS $c) {
            $config = array_merge($config, $c);
        }

        if (isset($config['disable_autowire_listener']) && $config['disable_autowire_listener']) {
            $builder->removeDefinition('simple_things.distribution.autowire_listener');
        }
    }
}