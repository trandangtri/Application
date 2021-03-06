<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Application\Business\Model\ApplicationCheckStep;

use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Shared\Config\Config;
use Symfony\Component\Process\Process;

class DeleteDatabase extends AbstractApplicationCheckStep
{

    /**
     * @return bool
     */
    public function run()
    {
        $this->info('Delete database');

        if (Config::get(ApplicationConstants::ZED_DB_ENGINE) === Config::get(ApplicationConstants::ZED_DB_ENGINE_PGSQL)) {
            $this->deletePostgresDatabaseIfExists();
        } else {
            $this->deleteMysqlDatabaseIfExists();
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return void
     */
    protected function closePostgresConnections()
    {
        $dropDatabaseCommand = sprintf(
            'psql -U %s -w  -c "SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pid <> pg_backend_pid() AND pg_stat_activity.datname = \'%s\';"',
            'postgres',
            Config::get(ApplicationConstants::ZED_DB_DATABASE)
        );

        $process = new Process($dropDatabaseCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return void
     */
    protected function deletePostgresDatabaseIfExists()
    {
        $this->closePostgresConnections();

        $dropDatabaseCommand = sprintf(
            'psql -U %s -w  -c "DROP DATABASE IF EXISTS \"%s\";"',
            'postgres',
            Config::get(ApplicationConstants::ZED_DB_DATABASE)
        );

        $process = new Process($dropDatabaseCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * @return void
     */
    protected function deleteMysqlDatabaseIfExists()
    {
        $con = new \PDO(
            Config::get(ApplicationConstants::ZED_DB_ENGINE)
            . ':host='
            . Config::get(ApplicationConstants::ZED_DB_HOST)
            . ';port=' . Config::get(ApplicationConstants::ZED_DB_PORT),
            Config::get(ApplicationConstants::ZED_DB_USERNAME),
            Config::get(ApplicationConstants::ZED_DB_PASSWORD)
        );

        $q = 'DROP DATABASE IF EXISTS ' . Config::get(ApplicationConstants::ZED_DB_DATABASE);
        $con->exec($q);
    }

}
