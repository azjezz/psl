<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

class CurrentExecTest extends TestCase
{
    public function testCurrentExe(): void
    {
        self::assertSame(realpath(__DIR__ . '/../../../vendor/phpunit/phpunit/phpunit'), Env\current_exec());
    }
}
