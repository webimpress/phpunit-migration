<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function implode;
use function preg_replace;
use function version_compare;

class VoidReturnTypeMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '8.0';

    private const FUNCTION_NAME = [
        'setUp',
        'tearDown',
        'setUpBeforeClass',
        'tearDownAfterClass',
        'assertPreConditions',
        'assertPostConditions',
        'onNotSuccessfulTest',
    ];

    public function migrate(string $content) : string
    {
        $content = preg_replace(
            '/(function\s+(' . implode('|', self::FUNCTION_NAME) . ')\s*\([^)]*\))([^:{]*{)/i',
            '\\1 : void\\3',
            $content
        );

        return $content;
    }

    public function canBeExecuted(string $phpUnitVersion, ?string $phpVersion) : bool
    {
        return version_compare($phpVersion, '7.0') >= 0
            || parent::canBeExecuted($phpUnitVersion, $phpVersion);
    }
}
