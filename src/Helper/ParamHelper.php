<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Helper;

use function array_combine;
use function array_fill;
use function array_pop;
use function array_shift;
use function count;
use function end;
use function in_array;
use function is_array;
use function rtrim;
use function strtolower;
use function token_get_all;

use const T_WHITESPACE;

class ParamHelper
{
    public function getParams(string $name, string $ex, array $keys) : array
    {
        $ex = '<?php ' . $ex;

        $tokens = token_get_all($ex);
        array_shift($tokens);

        $started = false;
        $open = null;
        $close = null;
        $stack = [];

        $function = '';

        $map = [']' => '[', '}' => '{', ')' => '('];
        $params = [];
        $param = '';

        // todo: check long array notation
        foreach ($tokens as $token) {
            $content = is_array($token) ? $token[1] : $token;

            $function .= $content;

            if (! $started) {
                if (strtolower($content) === strtolower($name)) {
                    $started = true;
                }
                continue;
            }

            if (! $open) {
                if ($content === '(') {
                    $open = true;
                }
                continue;
            }

            if (! $param && ! $stack && is_array($token) && $token[0] === T_WHITESPACE) {
                continue;
            }

            if (in_array($content, ['[', ']', '{', '}', '(', ')'], true)) {
                if (in_array($content, ['[', '{', '('], true)) {
                    $stack[] = $content;
                } else {
                    if (! $stack && $content === ')') {
                        // $close = true;
                        $params[] = rtrim($param);
                        break;
                    }

                    $last = end($stack);

                    if ($map[$content] === $last) {
                        array_pop($stack);
                    }
                }
            }

            if (! $stack && $content === ',') {
                $params[] = rtrim($param);
                $param = '';
                continue;
            }

            $param .= $content;
        }

        $len = count($params);
        $keysLen = count($keys);
        if ($len < $keysLen) {
            $fill = array_fill($len, $keysLen - $len, null);
            $params += $fill;
        }

        return array_combine($keys, $params) + ['__function__' => $function];
    }
}
