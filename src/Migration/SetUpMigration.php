<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function preg_replace;

class SetUpMigration extends AbstractMigration
{
    public function migrate(string $content) : string
    {
        $content = preg_replace(
            '/(public|protected)\s+function\s+setUp\s*\(/i',
            'protected function setUp(',
            $content
        );

        return $content;
    }
}
