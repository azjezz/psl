<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

use const PHP_ZTS;

final class ThreadSafetyTest extends TestCase
{
    public function testIsThreadSafe(): void
    {
        static::assertSame(PHP_ZTS === 1, Runtime\is_thread_safe());
    }
}
