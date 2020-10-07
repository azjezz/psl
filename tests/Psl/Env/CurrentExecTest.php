<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

use function realpath;

final class CurrentExecTest extends TestCase
{
    public function testCurrentExe(): void
    {
        $phpunit = __DIR__ . '/../../../vendor/phpunit/phpunit/phpunit';
        $phpunit = realpath($phpunit);

        self::assertSame($phpunit, Env\current_exec());
    }
}
