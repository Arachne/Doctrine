<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Codeception\Module\NetteDIModule;
use Codeception\Test\Unit;
use Doctrine\DBAL\Connection;

abstract class DatabaseTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    protected function _before(): void
    {
        $connection = $this->tester->grabService(Connection::class);
        $connection->executeQuery('CREATE TABLE article (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));');
        $connection->executeQuery('CREATE TABLE page (id INTEGER NOT NULL, article_id INTEGER DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_140AB6207294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE);');
        $connection->executeQuery('CREATE UNIQUE INDEX UNIQ_140AB6207294869C ON page (article_id);');
        $connection->executeQuery('INSERT INTO article (id, name) VALUES (1, "Lorem Ipsum");');
        $connection->executeQuery('INSERT INTO page (id, article_id) VALUES (1, 1);');
    }
}
