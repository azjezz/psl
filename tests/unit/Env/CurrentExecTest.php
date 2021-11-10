<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Filesystem;

use const PHP_OS_FAMILY;

final class CurrentExecTest extends TestCase
{
    public function testCurrentExe(): void
    {
        if ('Windows' === PHP_OS_FAMILY) {
            static::markTestSkipped('I do not want to bother :)');
        }

        $phpunit = __DIR__ . '/../../../vendor/bin/phpunit';
        $phpunit = Filesystem\canonicalize($phpunit);
        if (Filesystem\is_symbolic_link($phpunit)) {
            $phpunit = Filesystem\read_symbolic_link($phpunit);
        }

        static::assertSame($phpunit, Env\current_exec());
    }
}
