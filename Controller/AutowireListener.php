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

namespace SimpleThings\DistributionBundle\Controller;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AutowireListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onCoreController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller) || !isset($controller[0]) || !is_object($controller[0])) {
            return;
        }
        $this->injectControllerDependencies($controller[0]);
    }

    public function injectControllerDependencies($controller)
    {
        $reflClass = new \ReflectionObject($controller);
        if (!in_array("SimpleThings\DistributionBundle\Controller\AutowireController", $reflClass->getInterfaceNames())) {
            return;
        }

        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) AS $method) {
            /* @var $method ReflectionMethod */
            $methodName = strtolower($method->getName());
            if (substr($methodName, 0, 3) == "set" && substr($methodName, -6) != "action") {
                $injectParams = array();
                foreach ($method->getParameters() AS $param) {
                    /* @var $param \ReflectionParameter */
                    $name = preg_replace('([A-Z]{1})', '.\0', $param->getName());
                    if ($this->container->has($name)) {
                        $injectParams[] = $this->container->get($name);
                    } else if ($this->container->hasParameter($name)) {
                        $injectParams[] = $this->container->getParameter($name);
                    } else {
                        throw new \InvalidArgumentException(
                            "The argument '" . $param->getName() ."' to the magic controller injector method " .
                            "'" . $reflClass->getName()."#" . $method->getName() . "' is invalid because there is neither a service id " .
                            " nor a parameter name '" . $name ."' in the dependency injection container."
                        );
                    }
                }
                $method->invokeArgs($controller, $injectParams);
            }
        }
    }
}