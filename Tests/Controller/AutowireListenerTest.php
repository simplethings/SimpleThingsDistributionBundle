<?php

namespace SimpleThings\DistributionBundle\Tests\Controller;

use SimpleThings\DistributionBundle\Controller\AutowireController;
use SimpleThings\DistributionBundle\Controller\AutowireListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutowireListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testInject()
    {
        $obj = new \stdClass();
        $builder = new ContainerBuilder;
        $builder->set('object', $obj);
        $builder->set('parameter', 'foo');

        $controller = new InjectController;
        $listener = new AutowireListener($builder);
        $listener->injectControllerDependencies($controller);

        $this->assertSame($obj, $controller->object);
        $this->assertSame('foo', $controller->parameter);
    }

    public function testInjectNoService()
    {
        $builder = new ContainerBuilder;
        $controller = new InjectController;
        
        $listener = new AutowireListener($builder);

        $this->setExpectedException('InvalidArgumentException', "The argument 'object' to the magic controller injector method 'SimpleThings\DistributionBundle\Tests\Controller\InjectController#setServices' is invalid because there is neither a service id  nor a parameter name 'object' in the dependency injection container.");
        $listener->injectControllerDependencies($controller);
    }

    public function testNoInjectController()
    {
        $builder = new ContainerBuilder;
        $controller = new NoInjectController;

        $listener = new AutowireListener($builder);
        $listener->injectControllerDependencies($controller);
    }
}

class InjectController implements AutowireController
{
    public $object;
    public $parameter;

    public function setServices($object, $parameter)
    {
        $this->object = $object;
        $this->parameter = $parameter;
    }

    public function setAction($blub, $blab)
    {
        // never called by listener
        throw new \BadMethodCallException("SHould enver be called");
    }
}

class NoInjectController
{
    public function setServices($object, $parameter)
    {
        // never called by listener
        throw new \BadMethodCallException("SHould enver be called");
    }
}