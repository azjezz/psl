<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

use const PHP_DEBUG;

final class DebugTest extends TestCase
{
    public function testIsDebug(): void
    {
        static::assertSame(PHP_DEBUG === 1, Runtime\is_debug());
    }
}
