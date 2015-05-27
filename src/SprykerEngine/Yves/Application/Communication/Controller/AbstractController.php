<?php

namespace SprykerEngine\Yves\Application\Communication\Controller;

use SprykerEngine\Shared\Kernel\LocatorLocatorInterface;
use SprykerEngine\Yves\Application\Business\Application;
use SprykerEngine\Yves\Kernel\Communication\Factory;
use SprykerEngine\Shared\Messenger\Business\Model\MessengerInterface;
use SprykerEngine\Shared\Messenger\Communication\Presenter\TwigPresenter;
use SprykerEngine\Yves\Messenger\Communication\Presenter\YvesPresenter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use SprykerEngine\Yves\Kernel\AbstractDependencyContainer;
use SprykerFeature\Shared\ZedRequest\Client\Response as TransferResponse;
use Symfony\Component\HttpFoundation\Request;
use Generated\Yves\Ide\AutoCompletion;
use LogicException;
use ErrorException;
use Symfony\Component\Form\FormInterface;
use ArrayObject;
use SprykerFeature\Yves\Library\Session\TransferSession;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractController
{

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var AutoCompletion
     */
    protected $locator;

    /**
     * @var AbstractDependencyContainer
     */
    private $dependencyContainer;

    /**
     * @var MessengerInterface
     */
    private $messenger;

    /**
     * @param Application $app
     * @param Factory $factory
     * @param LocatorLocatorInterface $locator
     */
    public function __construct(Application $app, Factory $factory, LocatorLocatorInterface $locator)
    {
        $this->app = $app;
        $this->locator = $locator;
        $this->factory = $factory;

        $this->messenger = $this->locator->messenger()->sdk()->createMessenger();

        new YvesPresenter(
            $this->messenger,
            $this->locator->glossary()->sdk()->createTranslator($app['locale']),
            $this->getTwig()
        );

        if ($factory->exists('DependencyContainer')) {
            $this->dependencyContainer = $factory->create('DependencyContainer', $factory, $locator);
        }
    }

    /**
     * TODO Wrong place! Needs to be in Newrelic
     */
    public function disableLoggingToNewRelic()
    {
        \SprykerFeature_Shared_Library_NewRelic_Api::getInstance()->markIgnoreApdex();
        \SprykerFeature_Shared_Library_NewRelic_Api::getInstance()->markIgnoreTransaction();
    }

    /**
     * @return Factory
     */
    protected function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param string $path
     * @param array  $parameters
     * @param int    $code
     *
     * @return RedirectResponse
     */
    protected function redirectResponseInternal($path, $parameters = [], $code = 302)
    {
        return new RedirectResponse($this->getApplication()->path($path, $parameters), $code);
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        return $this->app;
    }

    /**
     * @return ArrayObject
     */
    protected function getCookieBag()
    {
        return $this->app->getCookieBag();
    }

    /**
     * @return TransferSession
     */
    protected function getTransferSession()
    {
        return $this->app->getTransferSession();
    }

    /**
     * @return string
     */
    protected function getLocale()
    {
        return $this->app['locale'];
    }

    /**
     * @return mixed
     */
    protected function getTranslator()
    {
        return $this->getApplication()['translator'];
    }

    /**
     * @param string $absoluteUrl
     * @param int    $code
     *
     * @return RedirectResponse
     */
    protected function redirectResponseExternal($absoluteUrl, $code = 302)
    {
        return new RedirectResponse($absoluteUrl, $code);
    }

    /**
     * @param null $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function jsonResponse($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function viewResponse(array $data = [])
    {
        return $data;
    }

    /**
     * @param TransferResponse $transferResponse
     */
    protected function addMessagesFromZedResponse(TransferResponse $transferResponse)
    {
        $this->getMessenger()->addMessagesFromResponse($transferResponse);
    }

    /**
     * @return MessengerInterface
     */
    private function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @return FlashMessageHelper
     */
    private function createFlashMessageHelper()
    {
        $flashBag = $this->getApplication()->getSession()->getFlashBag();
        // TODO Use factory to get FlashMessageHelper
        return new FlashMessageHelper($flashBag, $this->getApplication()['translator']);
    }

    /**
     * @param $message
     * @return $this
     * @throws ErrorException
     */
    protected function addMessageSuccess($message)
    {
        $this->getMessenger()->success($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     * @throws ErrorException
     */
    protected function addMessageWarning($message)
    {
        $this->getMessenger()->warning($message);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     * @throws ErrorException
     */
    protected function addMessageError($message)
    {
        $this->getMessenger()->error($message);

        return $this;
    }

    /**
     * @param string $type
     * @param null $data
     * @param array $options
     *
     * @return FormInterface
     *
     * @deprecated will be removed when we have our ComFactory
     */
    protected function createForm($type = 'form', $data = null, array $options = array())
    {
        return $this->getApplication()->createForm($type, $data, $options);
    }

    /**
     * TODO rethink
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
     * @param Request $request
     * @return string
     */
    protected function getSecurityError(Request $request)
    {
        return $this->app['security.last_error']($request);
    }

    /**
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        $securityContext = $this->getApplication()['security'];
        if (is_null($securityContext)) {
            throw new LogicException('Security is not enabled!');
        }

        return $securityContext;
    }

    /**
     * @return UserInterface
     */
    protected function getUser()
    {
        $securityContext = $this->getSecurityContext();
        $token = $securityContext->getToken();
        if (is_null($token)) {
            throw new LogicException("No logged in user found.");
        }

        return $token->getUser();
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
     * @param string $viewPath
     * @param array $parameters
     *
     * @return Response
     */
    protected function renderView($viewPath, array $parameters = [])
    {
        return $this->app->render($viewPath, $parameters);
    }

    /**
     * @return AutoCompletion
     */
    protected function getLocator()
    {
        return $this->locator;
    }

    /**
     * @return AbstractDependencyContainer
     */
    protected function getDependencyContainer()
    {
        return $this->dependencyContainer;
    }

    /**
     * @return Twig_Environment
     * @throws LogicException
     */
    private function getTwig()
    {
        $twig = $this->getApplication()['twig'];
        if (is_null($twig)) {
            throw new LogicException('Twig environment not set up.');
        }

        return $twig;
    }


}
