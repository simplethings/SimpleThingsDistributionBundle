<?php
/**
 * SimpleThings DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eberlei@simpletihngs.de so I can send you a copy immediately.
 */

namespace SimpleThings\DistributionBundle\Util;

/**
 * Global method to create new datetime instances from. Allows
 * to manipulate time for testing purposes.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class DateTime
{
    /**
     * @var DateTime
     */
    static private $now;

    /**
     * @return \DateTime
     */
    static public function now()
    {
        if (self::$now === null) {
            self::$now = new \DateTime("now");
        }

        return clone self::$now;
    }

    /**
     * Set the testing date
     *
     * Setting DateTime with Unix-Timestamp: new DateTime('@1234567890');
     *
     * @param \DateTime $date
     */
    static public function setTestingNow(\DateTime $date = null)
    {
        self::$now = $date;
    }
}

