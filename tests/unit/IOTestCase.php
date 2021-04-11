<?php

declare(strict_types=1);

namespace Psl\Tests\Unit;

use PHPUnit\Framework\TestCase;

abstract class IOTestCase extends TestCase
{
    protected function setUp(): void
    {
        if ('Windows' === PHP_OS_FAMILY) {
            static::markTestSkipped('Test cannot be executed on windows.');
        }
    }
}
