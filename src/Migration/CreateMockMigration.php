<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function implode;
use function preg_replace;

class CreateMockMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '5.4';

    public function migrate(string $content) : string
    {
        $requiredCalls = [
            'disableOriginalConstructor',
            'disableOriginalClone',
            'disableArgumentCloning',
            'disallowMockingUnknownTypes',
        ];

        $pattern = '/->\s*getMockBuilder\s*\((.+?)\)'
            . '(\s*->\s*(' . implode('|', $requiredCalls) . ')\s*\(\s*\)){4}'
            . '\s*->\s*getMock\s*\(\s*\)'
            . '/is';

        return preg_replace($pattern, '->createMock(\\1)', $content);
    }
}
