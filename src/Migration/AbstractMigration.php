<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function version_compare;

abstract class AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '5.0';

    abstract public function migrate(string $content) : string;

    public function canBeExecuted(string $phpUnitVersion, ?string $phpVersion) : bool
    {
        return version_compare($phpUnitVersion, static::PHPUNIT_VERSION_REQUIRED) >= 0;
    }
}
