# SimpleThings Distribution Bundle

## Repository Service Detector

The bundle ships a dependency injection extension that automatically detects services
for the repositories of Doctrine ORM entities. The convention for registration is
'{bundle_alias}.repository.{entity}', everything in lowercase.

## Controller Utils

The service `simple_things.distribution.controller_utils` implements all the methods that the Base Controller of the Framework Bundle has with
the following exceptions:

    * No access to the container possible through `has()` and `get()`
    * Added method `getRestView()` to access the fos_rest.view service if defined.
    * Added methods `isPut()`, `isPost()`, `isDelete()` and `isXmlHttpRequest()`.
    * Added methods `getUser()` and `isGranted($attributes, $object = null)` that checks for permissions
    * Added methods for throwing more http related exceptions
    * Added method `getSession()`
    * Added method `getLogger()`

## Controller

By Symfony2 default controllers are not service and services are grabbed through a service-locator approach
by directly accessing the Symfony DI Container. This is very convenient, but leads to hard to maintain code
in the long run.

This bundle ships a SimpleThings\DistributionBundle\Controller\Controller that is automatically registered
as a service based on the "{bundle_alias}.controller.{controller_name}" convention. This controller
is automatically injected the Controller#utils variable as the controller utils service.

For simplicity there is also Controller#__call implemented that delegates to the utils service for
as much API compability as possible to the default controller.

    use SimpleThings\DistributionBundle\Controller\Controller;

    /**
     * Controller to access jira instances through a HTTP-JSON interface
     *
     * @Extra\Route(service="whitewashing.controller.jira")
     */
    class JiraController extends Controller
    {
        private $jiraFactory;

        public function __construct($jiraFactory)
        {
            $this->jiraFactory = $jiraFactory;
        }

        /**
         * @Extra\Route("/jira/projects", name="ww_jira_projects")
         * @Extra\Method("GET")
         */
        public function projectsAction(Request $request)
        {
        }
    }
