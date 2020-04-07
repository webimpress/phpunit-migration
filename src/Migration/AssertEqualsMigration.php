<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use Webimpress\PHPUnitMigration\Helper\ParamHelper;

use function preg_match;
use function sprintf;
use function str_replace;
use function strtolower;

class AssertEqualsMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '7.5';

    public function migrate(string $content) : string
    {
        $paramHelper = new ParamHelper();

        $offset = 0;
        while (preg_match('/(assert(Not)?Equals)\s*\(.*/is', $content, $matches, 0, $offset)) {
            $p = $paramHelper->getParams(
                $matches[1],
                $matches[0],
                [
                    'expected',
                    'actual',
                    'message', // '',
                    'delta', // 0.0
                    'maxDepth', // 10
                    'canonicalize', // false
                    'ignoreCase', // false
                ]
            );

            $not = $matches[2] ?? '';

            if ($p['delta'] && $p['delta'] !== '0.0') {
                $replacement = sprintf(
                    'assert%sEqualsWithDelta(%s, %s, %s',
                    $not,
                    $p['expected'],
                    $p['actual'],
                    $p['delta']
                );
            } elseif ($p['canonicalize'] && strtolower($p['canonicalize']) !== 'false') {
                $replacement = sprintf('assert%sEqualsCanonicalizing(%s, %s', $not, $p['expected'], $p['actual']);
            } elseif ($p['ignoreCase'] && strtolower($p['ignoreCase']) !== 'false') {
                $replacement = sprintf('assert%sEqualsIgnoringCase(%s, %s', $not, $p['expected'], $p['actual']);
            } elseif ($p['message'] && ($p['message'] === '""' || $p['message'] === "''")) {
                $replacement = sprintf('assert%sEquals(%s, %s', $not, $p['expected'], $p['actual']);
            } else {
                ++$offset;
                continue;
            }

            if ($p['message'] && $p['message'] !== "''" && $p['message'] !== '""') {
                $replacement .= sprintf(', %s', $p['message']);
            }
            $replacement .= ')';

            $content = str_replace($p['__function__'], $replacement, $content);
        }

        return $content;
    }
}
