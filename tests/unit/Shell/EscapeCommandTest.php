<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Shell;

use Psl\Shell;
use Psl\Tests\Unit\IOTestCase;

final class EscapeCommandTest extends IOTestCase
{
    public function testEscapeCommand(): void
    {

        static::assertSame(
            "Hello, World!",
            Shell\execute(Shell\escape_command(PHP_BINARY), ['-r', 'echo "Hello, World!";'])
        );
    }
}
