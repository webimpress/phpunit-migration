<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function preg_replace;

class ExpectExceptionMessageRegExpMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '8.4';

    public function migrate(string $content) : string
    {
        return preg_replace(
            '/(->\s*)expectExceptionMessageRegExp(\s*\()/i',
            '\\1expectExceptionMessageMatches\\2',
            $content
        );
    }
}
