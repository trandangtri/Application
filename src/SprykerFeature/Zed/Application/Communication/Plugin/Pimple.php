<?php

namespace SprykerFeature\Zed\Application\Communication\Plugin;

use SprykerFeature\Shared\Application\Business\Application;
use SprykerEngine\Zed\Kernel\Communication\AbstractPlugin;

class Pimple extends AbstractPlugin
{

    /**
     * @var Application
     */
    protected static $application;

    /**
     * @param Application $application
     */
    public static function setApplication(Application $application)
    {
        self::$application = $application;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return self::$application;
    }
}
