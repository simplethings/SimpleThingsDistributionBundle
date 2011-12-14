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

namespace SimpleThings\DistributionBundle\Controller;

/**
 * Simplistic base controller that accepts the controller utils.
 *
 * The ControllerServiceCompilerPass will automatically check
 * for services extending this base controller and add this method
 * call if it was not added manually by the user.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class Controller
{
    /**
     * @var \SimpleThings\DistributionBundle\Controller\ControllerUtils
     */
    protected $utils;

    public function setControllerUtils(ControllerUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * This method makes sure the controller can be dropped
     * in late in the development and also to make controller API
     * consistent with Symfony2 defaults (to not confuse developers).
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->utils, $method), $args);
    }
}

