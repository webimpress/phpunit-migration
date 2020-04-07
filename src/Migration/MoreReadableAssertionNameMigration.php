<?php

declare(strict_types=1);

namespace Webimpress\PHPUnitMigration\Migration;

use function preg_replace;

class MoreReadableAssertionNameMigration extends AbstractMigration
{
    public const PHPUNIT_VERSION_REQUIRED = '9.1';

    /**
     * @var string[]
     */
    private $assertionMapping = [
        'assertNotIsReadable' => 'assertIsNotReadable',
        'assertNotIsWritable' => 'assertIsNotWritable',
        'assertDirectoryNotExists' => 'assertDirectoryDoesNotExist',
        'assertDirectoryNotIsReadable' => 'assertDirectoryIsNotReadable',
        'assertDirectoryNotIsWritable' => 'assertDirectoryIsNotWritable',
        'assertFileNotExists' => 'assertFileDoesNotExist',
        'assertFileNotIsReadable' => 'assertFileIsNotReadable',
        'assertFileNotIsWritable' => 'assertFileIsNotWritable',
        'assertRegExp' => 'assertMatchesRegularExpression',
        'assertNotRegExp' => 'assertDoesNotMatchRegularExpression',
    ];

    public function migrate(string $content) : string
    {
        foreach ($this->assertionMapping as $old => $new) {
            $content = preg_replace('/(\b)' . $old . '(\s*\()/is', '\\1' . $new . '\\2', $content);
        }

        return $content;
    }
}
