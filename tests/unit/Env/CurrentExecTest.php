<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Filesystem;

final class CurrentExecTest extends TestCase
{
    public function testCurrentExe(): void
    {
        $phpunit = __DIR__ . '/../../../tools/phpunit/vendor/bin/phpunit';
        $phpunit = Filesystem\canonicalize($phpunit);
        if (PHP_OS_FAMILY !== 'Windows' && Filesystem\is_symbolic_link($phpunit)) {
            $phpunit = Filesystem\read_symbolic_link($phpunit);
        }

        static::assertSame($phpunit, Env\current_exec());
    }
}
