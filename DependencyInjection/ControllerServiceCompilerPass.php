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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Automatically registers a controller that*/
class ControllerServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classes = array();
        foreach ($container->getServiceIds() as $serviceId) {
            $def = $container->getDefinition($serviceId);
#            $classes[$def->getClass()][] = ;
        }

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $namespace = substr($class, 0, strrpos($class, "\\"));
            $reflClass = new \ReflectionClass($class);
            $controllerDir = dirname($reflClass->getFileName()) . "/Controller";
            if (file_exists($controllerDir)) {
                // haz controllers
                foreach (scandir($controllerDir) as $file) {
                    if (substr($file, -14) != "Controller.php" && strlen($file) > 14) {
                        continue;
                    }
                    $className = $namespace . "\\Controller\\" . str_replace(".php", "", $file);
                    if (class_exists($className)) {
                        $controllerReflection = new \ReflectionClass($className);
                        if ($controllerReflection->isInstantiable()) {
                            continue;
                        }

                        $constructorMethod = $controllerReflection->getConstructor();
                        if ($constructorMethod) {
                            $params = array();
                            foreach ($constructorMethod->getParameters() AS $param) {
                                if ($param->getClass()) {
                                    $class = $param->getClass()->getName();
                                } else {
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

