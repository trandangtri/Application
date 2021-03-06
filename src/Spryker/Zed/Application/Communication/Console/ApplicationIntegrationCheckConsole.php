<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Communication\Console;

use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Shared\Config\Config;
use Spryker\Zed\Console\Business\Model\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Application\Business\ApplicationFacade getFacade()
 */
class ApplicationIntegrationCheckConsole extends Console
{

    const COMMAND_NAME = 'application:integration-check';
    const DESCRIPTION = 'Execute steps to check application';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::DESCRIPTION);

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!Config::get(ApplicationConstants::ALLOW_INTEGRATION_CHECKS, false)) {
            throw new \Exception('This command is only allowed to run in development environment');
        }

        $this->checkApplication(
            $this->getCheckSteps()
        );
    }

    /**
     * @param \Spryker\Zed\Application\Communication\Console\ApplicationCheckStep\AbstractApplicationCheckStep[] $steps
     *
     * @return void
     */
    private function checkApplication(array $steps)
    {
        $consoleLogger = new ConsoleLogger($this->output);

        foreach ($steps as $step) {
            $step->setLogger($consoleLogger);
            $step->run();
        }
    }

    /**
     * @return \Spryker\Zed\Application\Communication\Console\ApplicationCheckStep\AbstractApplicationCheckStep[]
     */
    protected function getCheckSteps()
    {
        return $this->getFacade()->getCheckSteps();
    }

}
