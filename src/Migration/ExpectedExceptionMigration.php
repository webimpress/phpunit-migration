<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use Webimpress\PHPUnitMigration\Helper\ParamHelper;

use function preg_match;
use function sprintf;
use function str_replace;
use function substr;

class ExpectedExceptionMigration extends AbstractMigration
{
    public function migrate(string $content) : string
    {
        $paramHelper = new ParamHelper();

        while (preg_match(
            '/->\s*setExpectedException\s*\(.+/is', //?\)\s*;/is',
            $content,
            $matches
        )) {
            $p = $paramHelper->getParams(
                'setExpectedException',
                '$this' . $matches[0],
                ['name', 'message', 'code']
            );

            $replacement = sprintf('->expectException(%s);', $p['name']);
            if ($p['message']) {
                $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionMessage(%s);', $p['message']);
            }
            if ($p['code']) {
                $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionCode(%s);', $p['code']);
            }

            $content = str_replace(
                substr($p['__function__'], 5),
                substr($replacement, 0, -1),
                $content
            );
        }

        while (preg_match(
            '/->\s*setExpectedExceptionRegexp\s*\(.+/is', //?\)\s*;/is',
            $content,
            $matches
        )) {
            $p = $paramHelper->getParams(
                'setExpectedExceptionRegexp',
                '$this' . $matches[0],
                ['name', 'message', 'code']
            );

            $replacement = sprintf('->expectException(%s);', $p['name']);
            if ($p['message']) {
                $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionMessageRegExp(%s);', $p['message']);
            }
            if ($p['code']) {
                $replacement .= "\n" . '        ' . sprintf('$this->expectExceptionCode(%s);', $p['code']);
            }

            $content = str_replace(
                substr($p['__function__'], 5),
                substr($replacement, 0, -1),
                $content
            );
        }

        return $content;
    }
}
