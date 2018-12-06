<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function preg_replace;

class CoversTagMigration extends AbstractMigration
{
    public function migrate(string $content) : string
    {
        $content = preg_replace(
            '/@covers\s+([a-z])/i',
            '@covers \\\\$1',
            $content
        );

        return $content;
    }
}
