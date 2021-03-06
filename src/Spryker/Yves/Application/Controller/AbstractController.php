<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Application\Controller;

use Spryker\Client\Kernel\ClassResolver\Client\ClientResolver;
use Spryker\Shared\Gui\Form\AbstractForm;
use Spryker\Yves\Application\Application;
use Spryker\Yves\Kernel\ClassResolver\Factory\FactoryResolver;
use Spryker\Yves\Kernel\Locator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController
{

    /**
     * @var \Spryker\Yves\Application\Application
     */
    private $application;

    /**
     * @var \Spryker\Yves\Kernel\AbstractFactory
     */
    private $factory;

    /**
     * @var \Spryker\Client\Kernel\AbstractClient
     */
    private $client;

    /**
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @param \Spryker\Yves\Application\Application $application
     *
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param string $path
     * @param array $parameters
     * @param int $code
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectResponseInternal($path, $parameters = [], $code = 302)
    {
        return new RedirectResponse($this->getApplication()->path($path, $parameters), $code);
    }

    /**
     * @return \Spryker\Yves\Application\Application
     */
    protected function getApplication()
    {
        return $this->application;
    }

    /**
     * @return string
     */
    protected function getLocale()
    {
        return $this->getApplication()['locale'];
    }

    /**
     * @param string $absoluteUrl
     * @param int $code
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectResponseExternal($absoluteUrl, $code = 302)
    {
        return new RedirectResponse($absoluteUrl, $code);
    }

    /**
     * @param mixed|null $data
     * @param int $status
     * @param array $headers
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function jsonResponse($data = null, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function viewResponse(array $data = [])
    {
        return $data;
    }

    /**
     * @param string $message
     *
     * @throws \ErrorException
     *
     * @return $this
     */
    protected function addSuccessMessage($message)
    {
        $this->getFlashMessenger()->addSuccessMessage($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @throws \ErrorException
     *
     * @return $this
     */
    protected function addInfoMessage($message)
    {
        $this->getFlashMessenger()->addInfoMessage($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @throws \ErrorException
     *
     * @return $this
     */
    protected function addErrorMessage($message)
    {
        $this->getFlashMessenger()->addErrorMessage($message);

        return $this;
    }

    /**
     * @param \Spryker\Shared\Gui\Form\AbstractForm $form
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function buildForm(AbstractForm $form, array $options = [])
    {
        return $this->getApplication()->buildForm($form, $options);
    }

    /**
     * @TODO rethink
     *
     * @param string $role
     *
     * @return mixed
     */
    protected function isGranted($role)
    {
        $security = $this->getApplication()['security'];
        if ($security) {
            return $security->isGranted($role);
        }

        throw new \LogicException('Security is not enabled!');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    protected function getSecurityError(Request $request)
    {
        $app = $this->getApplication();

        return $app['security.last_error']($request);
    }

    /**
     * @return bool
     */
    protected function hasUser()
    {
        $securityContext = $this->getSecurityContext();
        $token = $securityContext->getToken();

        return $token === null;
    }

    /**
     * @throws \LogicException
     *
     * @return mixed
     */
    protected function getSecurityContext()
    {
        $securityContext = $this->getApplication()['security'];
        if ($securityContext === null) {
            throw new \LogicException('Security is not enabled!');
        }

        return $securityContext;
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        $user = $this->getUser();
        if (is_string($user)) {
            return $user;
        }

        return $user->getUsername();
    }

    /**
     * @return mixed
     */
    protected function getUser()
    {
        $securityContext = $this->getSecurityContext();
        $token = $securityContext->getToken();
        if ($token === null) {
            throw new \LogicException('No logged in user found.');
        }

        return $token->getUser();
    }

    /**
     * @param string $viewPath
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderView($viewPath, array $parameters = [])
    {
        return $this->getApplication()->render($viewPath, $parameters);
    }

    /**
     * @deprecated Use DependencyProvider instead
     *
     * @return \Generated\Client\Ide\AutoCompletion
     */
    protected function getLocator()
    {
        return Locator::getInstance();
    }

    /**
     * @return \Spryker\Client\Kernel\AbstractClient
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = $this->resolveClient();
        }

        return $this->client;
    }

    /**
     * @throws \Spryker\Client\Kernel\ClassResolver\Client\ClientNotFoundException
     *
     * @return \Spryker\Client\Kernel\AbstractClient
     */
    private function resolveClient()
    {
        return $this->getClientResolver()->resolve($this);
    }

    /**
     * @return \Spryker\Client\Kernel\ClassResolver\Client\ClientResolver
     */
    private function getClientResolver()
    {
        return new ClientResolver();
    }

    /**
     * @return \Spryker\Yves\Kernel\AbstractFactory
     */
    protected function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = $this->resolveFactory();
        }

        return $this->factory;
    }

    /**
     * @throws \Spryker\Yves\Kernel\ClassResolver\Factory\FactoryNotFoundException
     *
     * @return \Spryker\Yves\Kernel\AbstractFactory
     */
    private function resolveFactory()
    {
        return $this->getFactoryResolver()->resolve($this);
    }

    /**
     * @return \Spryker\Yves\Kernel\ClassResolver\Factory\FactoryResolver
     */
    private function getFactoryResolver()
    {
        return new FactoryResolver();
    }

    /**
     * @return \Pyz\Yves\Application\Business\Model\FlashMessengerInterface
     */
    private function getFlashMessenger()
    {
        return $this->application['flash_messenger'];
    }

}
