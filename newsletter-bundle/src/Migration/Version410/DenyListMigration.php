<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\NewsletterBundle\Migration\Version410;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class DenyListMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        return $schemaManager->tablesExist('tl_newsletter_blacklist')
            && !$schemaManager->tablesExist('tl_newsletter_deny_list');
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schemaManager->renameTable('tl_newsletter_blacklist', 'tl_newsletter_deny_list');

        return $this->createResult(true);
    }
}
