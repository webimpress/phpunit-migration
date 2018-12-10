[![Build Status](https://travis-ci.com/webimpress/phpunit-migration.svg?branch=master)](https://travis-ci.com/webimpress/phpunit-migration)
[![Coverage Status](https://coveralls.io/repos/github/webimpress/phpunit-migration/badge.svg?branch=master)](https://coveralls.io/github/webimpress/phpunit-migration?branch=master)

# PHPUnit migration tool

Migrate project to the newest version of PHPUnit.

**[Work In Progress] Use it on the own risk :)**

## How to use the tool?

Clone the project:
```console
$ git clone https://github.com/webimpress/phpunit-migration.git
```

Go into the directory and install dependencies:
```console
$ cd phpunit-migration
$ composer install
```

To update your project to the newest version of PHPUnit go to your project directory and run:
```console
$ ../path/to/phpunit-migration/bin/phpunit-migration migrate
```

## What the tool is changing?

1. compose dependencies to the latest PHPUnit versions,
2. `\PHPUnit_Framework_TestCase` to namespaced `\PHPUnit\Framework\TestCase`,
3. `setExpectedException` to `expectException*`,
4. `setUp` and `tearDown` to `protected` and correct case (`setup` => `setUp` etc.),
5.  FQCN in `@cover` tag (i.e. `@covers MyClass` to `@covers \MyClass`),
6. `assertInternalType` and `assertNotInternalType` to more specific assertion method (PHPUnit 7.5+),
7. `getMock` to `getMockBuilder` with other required function calls (PHPUnit 5.4+),
8. `getMockBuilder(...)->...->getMock()` to `createMock(...)` if possible (PHPUnit 5.4+),
9. `assertEquals()` and `assertNotEquals()` with `$delta`, `$maxDepth`, `$canonicalize` and `$ignoreCase`
  parameters to more specific assertion method (PHPUnit 7.5),
10. TODO: `getMockBuilder(...)->...->setMethods(...)->getMock()` to `createPartialMock(...)` if possible
  (PHPUnit 5.5.3+),
11. TODO: `assertContains()` and `assertNotContains()` on `string` haystack to more specific assertion method
  (PHPUnit 7.5+),
12. TODO: `$this->assert` to `self::assert`.

## What the tool is NOT doing?

1. changing `PHPUnit_Framework_Error_*` classes
2. probably other things I don't remember now ;-)

> ### Note
>
> Please remember it is developer tool and it should be used
> only as a helper to migrate your tests to newer version
> of PHPUnit.
> Always after migration run all your test to verify if applied
> changes are right and your tests are still working!
