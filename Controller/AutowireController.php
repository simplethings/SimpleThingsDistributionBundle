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

/**
 * Marker interface for the AutowireListener, controllers of this kind are autoinjected services into methods set*().
 *
 * On core controller event a listener finds all `set*()` methods not ending with "Action" and
 * the variable names are checked against service names by replacing each upper-case character with "." + lower-case char. So
 * "doctrine.orm.default_entity_manager" is injected into `$doctrineOrmDefault_entity_manager`. Comparison is done lower-case.
 * Same works for container parameters. Service names are evaluated before parameters, there is no way to use "hidden" parameters.
 * If neither service nor parameter is found an exception is thrown
 */
interface AutowireController
{
    
}