<?php

namespace SimpleThings\DistributionBundle\Tests\Controller;

use SimpleThings\DistributionBundle\Controller\ControllerUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ControllerUtilsTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    /**
     * @var ControllerUtils
     */
    private $utils;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->utils = new ControllerUtils($this->container);
    }

    public function testGenerateUrl()
    {
        $route = 'foo';
        $parameters = array('foo' => 'bar');
        $absolute = true;

        $generatorMock = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $generatorMock->expects($this->once())->method('generate')->with($this->equalTo($route), $this->equalTo($parameters), $this->equalTo($absolute));
        $this->container->expects($this->once())->method('get')->with($this->equalTo('router'))->will($this->returnValue($generatorMock));

        $this->utils->generateUrl($route, $parameters, $absolute);
    }

    public function testForward()
    {
        $controller = "Foo:Bar:baz";
        $path = array('foo' => 'bar');
        $query = array('bar' => 'baz');

        $kernelMock = $this->getMock('Symfony\Bundle\FrameworkBundle\HttpKernel', array(), array(), '', false);
        $kernelMock->expects($this->once())->method('forward')->with($this->equalTo($controller), $this->equalTo($path), $this->equalTo($query));
        $this->container->expects($this->once())->method('get')->with($this->equalTo('http_kernel'))->will($this->returnValue($kernelMock));
        
        $this->utils->forward($controller, $path, $query);
    }

    public function testRedirect()
    {
        $response = $this->utils->redirect('http://foo/bar', 301);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testRenderView()
    {
        $view = "foo.bar";
        $parameters = array('foo' => 'bar');

        $templatingMock = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templatingMock->expects($this->once())->method('render')->with($this->equalTo($view), $this->equalTo($parameters));
        $this->container->expects($this->once())->method('get')->with($this->equalTo('templating'))->will($this->returnValue($templatingMock));

        $this->utils->renderView($view, $parameters);
    }

    public function testRender()
    {
        $view = "foo.bar";
        $parameters = array('foo' => 'bar');
        $response = new Response();

        $templatingMock = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templatingMock->expects($this->once())->method('renderResponse')->with($this->equalTo($view), $this->equalTo($parameters), $this->isInstanceOf(get_class($response)));
        $this->container->expects($this->once())->method('get')->with($this->equalTo('templating'))->will($this->returnValue($templatingMock));

        $this->utils->render($view, $parameters, $response);
    }

    public function testCreateNotFoundException()
    {
        $ex = $this->utils->createNotFoundException();
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', $ex);
    }

    public function testCreateAccessDeniedException()
    {
        $ex = $this->utils->createAccessDeniedException();
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException', $ex);
    }

    public function testCreateHttpException()
    {
        $ex = $this->utils->createNotFoundException();
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException', $ex);
    }

    public function testGetRequest()
    {
        $request = new Request();

        $this->container->expects($this->once())->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));

        $this->assertSame($request, $this->utils->getRequest());
    }

    public function testIsPost()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_METHOD' => 'POST'), '');

        $this->container->expects($this->once())->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));

        $this->utils->isPost();
    }

    public function testIsPut()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_METHOD' => 'PUT'), '');

        $this->container->expects($this->once())->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));

        $this->assertTrue($this->utils->isPut());
    }

    public function testIsDelete()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_METHOD' => 'DELETE'), '');

        $this->container->expects($this->once())->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));

        $this->assertTrue($this->utils->isDelete());
    }

    public function testIsXmlHttpRequest()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('HTTP_X-REQUESTED-WITH' => 'XMLHttpRequest'), '');

        $this->container->expects($this->once())->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));

        $this->assertTrue($this->utils->isXmlHttpRequest());
    }

    public function testGetSecurityContext()
    {
        $contextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->container->expects($this->once())->method('get')->with($this->equalTo('security.context'))->will($this->returnValue($contextMock));

        $this->assertSame($contextMock, $this->utils->getSecurityContext());
    }

    public function testGetUser()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $tokenMock = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenMock->expects($this->once())->method('getUser')->will($this->returnValue($user));

        $contextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $contextMock->expects($this->once())->method('getToken')->will($this->returnValue($tokenMock));

        $this->container->expects($this->once())->method('get')->with($this->equalTo('security.context'))->will($this->returnValue($contextMock));

        $this->assertSame($user, $this->utils->getUser());
    }

    public function testGetUserNoToken()
    {
        $contextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->container->expects($this->once())->method('get')->with($this->equalTo('security.context'))->will($this->returnValue($contextMock));

        $this->assertNull($this->utils->getUser());
    }

    public function testGetUserNoUserInterface()
    {
        $tokenMock = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenMock->expects($this->once())->method('getUser')->will($this->returnValue("anon."));

        $contextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $contextMock->expects($this->once())->method('getToken')->will($this->returnValue($tokenMock));

        $this->container->expects($this->once())->method('get')->with($this->equalTo('security.context'))->will($this->returnValue($contextMock));

        $this->assertNull($this->utils->getUser());
    }

    public function testIsGranted()
    {
        $object = new \stdClass();
        $attributes = array('foo' => 'bar');

        $contextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $contextMock->expects($this->once())->method('isGranted')->with($this->equalTo($attributes), $this->equalTo($object));

        $this->container->expects($this->once())->method('get')->with($this->equalTo('security.context'))->will($this->returnValue($contextMock));

        $this->utils->isGranted($attributes, $object);
    }

    public function testGetSession()
    {
        $sessionMock = $this->getMock('Symfony\Component\HttpFoundation\Session', array(), array(), '', false);

        $this->container->expects($this->once())->method('get')->with($this->equalTo('session'))->will($this->returnValue($sessionMock));

        $this->assertSame($sessionMock, $this->utils->getSession());
    }

    public function testGetLogger()
    {
        $loggerMock = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '', false);

        $this->container->expects($this->once())->method('get')->with($this->equalTo('logger'))->will($this->returnValue($loggerMock));

        $this->assertSame($loggerMock, $this->utils->getLogger());
    }
}
