<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Communication\Plugin\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Shared\Library\Context;
use Spryker\Shared\Library\DateFormatter;
use Spryker\Shared\Library\Twig\DateFormatterTwigExtension;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\Application\Business\ApplicationFacade getFacade()
 * @method \Spryker\Zed\Application\Communication\ApplicationCommunicationFactory getFactory()
 */
class DateFormatterServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $dateFormatter = new DateFormatter(Context::getInstance(Context::CONTEXT_ZED));

        $app['dateFormatter'] = $app->share(function () use ($dateFormatter) {
            return $dateFormatter;
        });

        $app['twig'] = $app->share(
            $app->extend('twig', function (\Twig_Environment $twig) use ($dateFormatter) {
                $twig->addExtension(new DateFormatterTwigExtension($dateFormatter));

                return $twig;
            })
        );
    }

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
    }

}
