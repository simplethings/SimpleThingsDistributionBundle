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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ControllerUtils
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $name The name of the route
     * @param array $parameters An array of parameters
     * @param Boolean $absolute Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generateUrl($route, array $parameters = array(), $absolute = false)
    {
        return $this->container->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array $path An array of path parameters
     * @param array $query An array of query parameters
     *
     * @return Response A Response instance
     */
    public function forward($controller, array $path = array(), array $query = array())
    {
        return $this->container->get('http_kernel')->forward($controller, $path, $query);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return string The renderer view
     */
    public function renderView($view, array $parameters = array())
    {
        return $this->container->get('templating')->render($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     * throw $this->createNotFoundException('Page not found!');
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Create Access Denied (HTTP 403) exception
     * 
     * @param string $message
     * @param \Exception $previous
     * @return AccessDeniedHttpException
     */
    public function createAccessDeniedException($message = 'Access denied', \Exception $previous = null)
    {
        return new AccessDeniedHttpException($message, $previous);
    }

    /**
     * Create HTTP Exception
     * 
     * @param string $message
     * @param int $statusCode
     * @param \Exception $previous
     * @return \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function createHttpException($message, $statusCode, \Exception $previous = null)
    {
        return new HttpException($statusCode, $message, $previous);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type The built type of the form
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance
     *
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }

    /**
     * Shortcut to return the request service.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->getRequest()->getMethod() == 'POST';
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return $this->getRequest()->getMethod() == 'PUT';
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->getRequest()->getMethod() == 'DELETE';
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * @return FOS\RestBundle\View\ViewInterface
     */
    public function getRestView()
    {
        return $this->container->get('fos_rest.view');
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->container->get('security.context');
    }

    /**
     * Is current user granted certain attributes on a certain object or in general?
     *
     * @param mixed $attributes
     * @param object $object
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->getSecurityContext()->isGranted($attributes, $object);
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser()
    {
        $token = $this->getSecurityContext()->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                return $user;
            }
        }
        return null;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session
     */
    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @return \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->container->get('logger');
    }
}