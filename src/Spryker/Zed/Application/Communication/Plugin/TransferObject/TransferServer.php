<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Communication\Plugin\TransferObject;

use Spryker\Shared\ZedRequest\Client\ResponseInterface;
use Spryker\Zed\ZedRequest\Business\Client\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class TransferServer
{

    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var bool
     */
    protected $repeatIsActive = false;

    /**
     * @var \Spryker\Shared\ZedRequest\Client\RequestInterface
     */
    private $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $httpRequest;

    /**
     * @var \Spryker\Shared\ZedRequest\Client\ResponseInterface|\Spryker\Shared\Library\Communication\Response
     */
    protected $response;

    /**
     * @var \Spryker\Zed\Application\Communication\Plugin\TransferObject\Repeater
     */
    private $repeater;

    /**
     * @param \Spryker\Zed\Application\Communication\Plugin\TransferObject\Repeater $repeater
     */
    private function __construct(Repeater $repeater)
    {
        $this->repeater = $repeater;
    }

    /**
     * @param \Spryker\Zed\Application\Communication\Plugin\TransferObject\Repeater|null $repeater
     *
     * @return $this
     */
    public static function getInstance(Repeater $repeater = null)
    {
        if (self::$instance) {
            return self::$instance;
        }

        if ($repeater === null) {
            $repeater = new Repeater();
        }

        self::$instance = new static($repeater);

        return self::$instance;
    }

    /**
     * @return void
     */
    public function activateRepeating()
    {
        $this->repeatIsActive = true;
    }

    /**
     * @return \Spryker\Zed\ZedRequest\Business\Client\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            if ($this->repeatIsActive) {
                $this->request = new Request(
                    $this->repeater->getRepeatData($this->getHttpRequest()->query->get('mvc'))['params']
                );
            } else {
                $transferValues = json_decode($this->getHttpRequest()->getContent(), true);
                $this->request = new Request($transferValues);
                $this->repeater->setRepeatData($this->request, $this->httpRequest);
            }
        }

        return $this->request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function getHttpRequest()
    {
        if ($this->httpRequest === null) {
            throw new \LogicException('No Http Request found in TransferServer. Maybe you try to access data from it before the request object is injected.');
        }

        return $this->httpRequest;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     *
     * @return $this
     */
    public function setRequest(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;

        return $this;
    }

    /**
     * @param \Spryker\Shared\ZedRequest\Client\ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function send()
    {
        $jsonResponse = new JsonResponse($this->response->toArray());
        if ($this->repeatIsActive) {
            $jsonResponse->setEncodingOptions(JSON_PRETTY_PRINT);
        }

        return $jsonResponse;
    }

}
