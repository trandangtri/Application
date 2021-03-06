<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Communication\Plugin\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Shared\Config\Config;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Application\Business\ApplicationFacade getFacade()
 * @method \Spryker\Zed\Application\Communication\ApplicationCommunicationFactory getFactory()
 */
class SslServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{

    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
    }

    /**
     * @param \Silex\Application $app
     *
     * @throws \Exception
     *
     * @return void
     */
    public function boot(Application $app)
    {
        $app->before(function (Request $request) use ($app) {
            if ($this->shouldBeSsl($request)) {
                $url = 'https://' . $request->getHttpHost() . $request->getRequestUri();

                return new RedirectResponse($url, 301);
            }
        });
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function shouldBeSsl(Request $request)
    {
        return Config::get(ApplicationConstants::ZED_SSL_ENABLED)
            && !$this->isSecure($request)
            && !$this->isYvesRequest($request)
            && !$this->isExcludedFromRedirection($request, Config::get(ApplicationConstants::ZED_SSL_EXCLUDED));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    protected function isYvesRequest(Request $request)
    {
        return (bool)$request->headers->get('X-Yves-Host');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    protected function isSecure(Request $request)
    {
        $https = $request->server->get('HTTPS', false);
        $xForwardedProto = $request->server->get('X-Forwarded-Proto', false);

        return ($https && ($https === 'on' || $https === 1) || $xForwardedProto && $xForwardedProto === 'https');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $excluded
     *
     * @return bool
     */
    protected function isExcludedFromRedirection(Request $request, array $excluded)
    {
        return in_array($request->attributes->get('module') . '/' . $request->attributes->get('controller'), $excluded);
    }

}
