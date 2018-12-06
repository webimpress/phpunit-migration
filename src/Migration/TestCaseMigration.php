<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use PHPUnit\Framework\TestCase;

use function preg_replace;
use function str_replace;

class TestCaseMigration extends AbstractMigration
{
    public function migrate(string $content) : string
    {
        $content = str_replace('PHPUnit_Framework_TestCase', TestCase::class, $content);

        $content = preg_replace(
            '/extends\s+PHPUnit\\\\Framework\\\\TestCase/',
            'extends TestCase',
            $content
        );

        $content = preg_replace(
            '/((abstract\s+)?class\s+.*?extends\s+)\\\\PHPUnit\\\\Framework\\\\TestCase/',
            'use PHPUnit\Framework\TestCase;' . "\n\n" . '\\1TestCase',
            $content
        );

        $content = preg_replace(
            '/(use\s+[^;{]+?\\\\([^\s;]+?))\s+as\s+\2\s*;/i',
            '\\1;',
            $content
        );

        return $content;
    }
}
