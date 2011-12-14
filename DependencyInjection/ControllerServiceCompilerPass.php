<?php

/**
 * SimpleThings Distribution Bundle
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
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Automatically registers a controller as a service  that extend the SimpleThings
 * Base Controller and set a method call to setControllerUtils and if defined
 * optionally setContainer().
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ControllerServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classes = array();
        foreach ($container->getServiceIds() as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $def = $container->getDefinition($serviceId);
                $classes[$def->getClass()][] = $serviceId;
            }
        }

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $namespace = substr($class, 0, strrpos($class, "\\"));
            $reflClass = new \ReflectionClass($class);
            $controllerDir = dirname($reflClass->getFileName()) . "/Controller";
            if (file_exists($controllerDir)) {
                // haz controllers
                foreach (scandir($controllerDir) as $file) {
                    $this->registerController($file, $namespace, $container);
                }
            }
        }
    }

    private function registerController($file, $namespace, $container)
    {
        if (substr($file, -14) != "Controller.php" && strlen($file) == 14) {
            return;
        }
        $controllerName = str_replace(".php", "", $file);
        $className = $namespace . "\\Controller\\" . $controllerName;
        if (!class_exists($className) || !is_subclass_of($className, "SimpleThings\\DistributionBundle\\Controller\\Controller")) {
            return;
        }

        if (preg_match('(([a-zA-Z0-9\\\\]+Bundle))', $className, $match)) {
            $alias = Container::underscore(str_replace(array("\\", "Bundle"), "", $match[1]));

            $service = $alias . ".controller." . strtolower($controllerName);
            if ($container->hasDefinition($service)) {
                $def = $container->getDefinition($service);
            } else {
                $def = new Definition($className);
            }

            if (!$def->hasMethodCall('setControllerUtils')) {
                $def->addMethodCall('setControllerUtils', array(new Reference('simple_things_distribution.controller_utils')));
            }
            if (method_exists($className, 'setContainer') && !$def->hasMethodCall('setContainer')) {
                $def->addMethodCall('setContainer', array(new Reference('service_container')));
            }

            $container->setDefinition($service, $def);
        }
    }
}

