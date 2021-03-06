<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Application\Communication\Controller\Fixtures;

use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Spryker\Zed\Assertion\Business\AssertionFacade;

class MockController extends AbstractController
{

    /**
     * @param mixed $id
     *
     * @return int
     */
    public function indexAction($id)
    {
        return $this->castId($id);
    }

    /**
     * @return \Spryker\Zed\Assertion\Business\AssertionFacade
     */
    protected function getAssertion()
    {
        return new AssertionFacade();
    }

}
