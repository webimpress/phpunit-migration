<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function preg_replace;

class TearDownMigration extends AbstractMigration
{
    public function migrate(string $content) : string
    {
        $content = preg_replace(
            '/(public|protected)\s+function\s+tearDown\s*\(/i',
            'protected function tearDown(',
            $content
        );

        return $content;
    }
}
