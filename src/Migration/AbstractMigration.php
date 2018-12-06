<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

abstract class AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '5.0';

    abstract public function migrate(string $content) : string;
}
