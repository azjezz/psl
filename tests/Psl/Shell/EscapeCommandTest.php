<?php

declare(strict_types=1);

namespace Psl\Tests\Shell;

use PHPUnit\Framework\TestCase;
use Psl\Shell;

final class EscapeCommandTest extends TestCase
{
    public function testEscapeCommand(): void
    {
        static::assertSame(
            "Hello, World!",
            Shell\execute(Shell\escape_command(PHP_BINARY), ['-r', 'echo "Hello, World!";'])
        );
    }
}
