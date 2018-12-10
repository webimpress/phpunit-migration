<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use Webimpress\PHPUnitMigration\Helper\ParamHelper;

use function in_array;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strtolower;
use function substr_count;

class GetMockMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '5.4';

    public function migrate(string $content) : string
    {
        $paramHelper = new ParamHelper();

        while (preg_match(
            '/\$this\s*->\s*getMock\s*\(.+/is',
            $content,
            $matches
        )) {
            $p = $paramHelper->getParams(
                'getMock',
                $matches[0],
                [
                    'class',
                    'methods', // []
                    'args', // []
                    'name', // ''
                    'callOriginConstructor', // true
                    'callOriginalClone', // true
                    'callAutoload', // true
                    'cloneArguments', // false
                    'callOriginalMethods', // false
                    'proxyTarget', // null
                ]
            );

            $replacement = sprintf('$this->getMockBuilder(%s)', $p['class']);
            if ($p['methods']) {
                if (strtolower($p['methods']) === 'null') {
                    $replacement .= "\n" . '            ->setMethods()';
                } elseif (! in_array(strtolower($p['methods']), ['[]', 'array()'], true)) {
                    $replacement .= "\n" . sprintf('            ->setMethods(%s)', $p['methods']);
                }
            }

            if ($p['args'] && $p['args'] !== '[]' && strtolower($p['args']) !== 'array()') {
                $replacement .= "\n" . sprintf('            ->setConstructorArgs(%s)', $p['args']);
            }

            if ($p['name'] && $p['name'] !== '""' && $p['name'] !== "''") {
                $replacement .= "\n" . sprintf('            ->setMockClassName(%s)', $p['name']);
            }

            if ($p['callOriginConstructor'] && strtolower($p['callOriginConstructor']) !== 'true') {
                $replacement .= "\n" . '            ->disableOriginalConstructor()';
            }

            if ($p['callOriginalClone'] && strtolower($p['callOriginalClone']) !== 'true') {
                $replacement .= "\n" . '            ->disableOriginalClone()';
            }

            if ($p['callAutoload'] && strtolower($p['callAutoload']) !== 'true') {
                $replacement .= "\n" . '            ->disableAutoload()';
            }

            if ($p['cloneArguments'] && strtolower($p['cloneArguments']) !== 'false') {
                $replacement .= "\n" . '            ->enableArgumentCloning()';
            }

            if ($p['callOriginalMethods'] && strtolower($p['callOriginalMethods']) !== 'false') {
                $replacement .= "\n" . '            ->enableProxyingToOriginalMethods()';
            }

            if ($p['proxyTarget'] && strtolower($p['proxyTarget']) !== 'null') {
                $replacement .= "\n" . sprintf('            ->setProxyTarget(%s)', $p['proxyTarget']);
            }

            $replacement .= "\n" . '            ->getMock()';

            if (substr_count($replacement, "\n") === 1) {
                $replacement = preg_replace('/\n\s+/', '', $replacement);
            }

            $content = str_replace($p['__function__'], $replacement, $content);
        }

        return $content;
    }
}
