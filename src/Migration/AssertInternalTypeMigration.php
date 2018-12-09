<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use Webimpress\PHPUnitMigration\Helper\ParamHelper;

use function preg_match;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strstr;
use function strtolower;
use function substr;
use function trim;
use function ucfirst;

class AssertInternalTypeMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '7.5';

    /**
     * @var string[]
     */
    private $paramMapping = [
        'boolean' => 'bool',
        'double' => 'float',
        'integer' => 'int',
        'real' => 'float',
    ];

    public function migrate(string $content) : string
    {
        $paramHelper = new ParamHelper();

        while (preg_match('/(assert(Not)?InternalType)\s*\(.*/is', $content, $matches)) {
            $p = $paramHelper->getParams(
                $matches[1],
                $matches[0],
                ['expected', 'actual', 'message']
            );

            $not = $matches[2] ?? '';
            $type = strtolower(substr(trim($p['expected']), 1, -1));
            $name = ucfirst($this->paramMapping[$type] ?? $type);

            $replacement = sprintf('assertIs%s%s(', $not, $name);

            $content = str_replace(
                preg_replace("/\n[ ]+$/", '', strstr($p['__function__'], $p['actual'], true)),
                $replacement,
                $content
            );
        }

        return $content;
    }
}
