# SimpleThings Distribution Bundle

Helps you quickstart applications by bundling all the common assets that we use at SimpleThings aswell as useful commands and services.

This bundle ships with the following code:

    * Blueprint v1.0.1 (http://www.blueprintcss.org/)
    * FamFamFam Silk Icons (http://www.famfamfam.com/lab/icons/silk/)
    * FamFamFam Sprite for Blueprint (http://www.ajaxbestiary.com/Labs/SilkSprite/)
    * Chosen for jQuery (https://github.com/harvesthq/chosen/tree/master/chosen)
    * qTip for jQuery 1.x (http://craigsworks.com/projects/qtip/)
    * jQuery Notify Plugin (http://www.erichynds.com/jquery/a-jquery-ui-growl-ubuntu-notification-widget/)
    * Aristo jQuery UI Theme (https://github.com/taitems/Aristo-jQuery-UI-Theme)

Thanks to all the authors of these awesome UI libraries.

You have to mash these assets together yourself using assetic or plain asset() command. Just use what you need.
Additionally for the jQuery related stuff you need jQuery from Google CDN (http://code.google.com/apis/libraries/devguide.html#jquery)

## Service Definition Generator

Early in projects you are often doing repetitive service generation tasks, such as defining Doctrine repositories
as services in the DIC. This command will print the YAML or XML definitions of these services based on an convention.
For each entity/document a repository is printed with "{bundle}.repository.{entity}". If this service already skipped
the printing is skipped.

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

## Autowiring Controllers

The default controller in Symfony 2 is annoying, because it "only" injects the container and doesn't offer any auto-completion.
We want controllers that are explicit about their dependencies, but we don't want to define controllers as services
(since the syntax is not so nice and requires changing the routing definitions). However we also do not use annotations
in our projects, so this autowiring controller resolver works a bit different:

During Controller instantation all `set*()` methods not ending with "Action" are searched and
the variable names are checked against service names by replacing each upper-case character with "." + lower-case char. So
"doctrine.orm.default_entity_manager" is injected into `$doctrineOrmDefault_entity_manager`. Comparison is done lower-case.
Same works for container parameters. Service names are evaluated before parameters, there is no way to use "hidden" parameters.
If neither service nor parameter is found an exception is thrown

This is only done to controllers with an `SimpleThings\DistributionBundle\Controller\AutowireController` marker interface.

Additionally there are some conventions:

    * `$entityManager` and `$em` get injected the default entity manager.
    * `$controllerUtils` gets injected the `simple_things_distribution.controller_utils` service
    * `$documentManager` and `$dm` gets injected the default CouchDB manager (We don't use mongoDb)
    * `$connection` gets injected the default DBAL connection.
    * `$view` gets injected the `fos_rest.view` service
    * `$yRepository` gets injected the repository of the entity/document with the shortname $y. This can lead to clashes, which you have to resolve by setting service aliases manually. Otherwise the first wins.

This is controlled by automatically setting aliases to the services.

Other common services that don't need to be renamed because their service name is already that short in the container:

    * `$router`
    * `$serviceContainer`
    * `$request`
    * `$mailer`
    * `$templating`
    * `$formFactory`
    * `$logger`
    * `$session`
    * `$securityContext`
    * `$eventDispatcher`
    * `$filesystem`
    * `$translator`
    * `$validator`
    * `$doctrine`

Some questions that pop up on this are probably:

    * Why are the typehints not used?

        Typehints may have different implementations, and the service container makes it complex to get to the service based on class name.
        Also the decoupling of service names and class implementations is a really nice benefit and controller code would break if you change the impl.
        of a service.

    * Why is the constructor not checked?

        This way we can hook a simple controller event listener and don't have to replace the controller resolving.

    * Do i have to create that many setters?

        No, a method `setServices($request, $router, $entityManager, $view)` for example gets injected all the services.

    * Now do i have to inject all the framework/mvc utils all the time?

        No, we use a convenience service that ships all the necessary controller utilities/services, called `SimpleThings\DistributionBundle\Controller\ControllerUtils`.
        You can inject it with the `$controllerUtils` variable.
