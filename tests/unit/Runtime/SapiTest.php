<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

use const PHP_SAPI;

final class SapiTest extends TestCase
{
    public function testSapi(): void
    {
        static::assertSame(PHP_SAPI, Runtime\get_sapi());
    }
}
