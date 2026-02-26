<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Process;

use PHPUnit\Framework\TestCase;
use Psl\Process\ExitStatus;
use Psl\Process\Output;

final class OutputTest extends TestCase
{
    public function testProperties(): void
    {
        $status = new ExitStatus(0);
        $output = new Output($status, 'hello', 'world');

        static::assertSame($status, $output->status);
        static::assertSame('hello', $output->stdout);
        static::assertSame('world', $output->stderr);
    }

    public function testEmptyOutput(): void
    {
        $status = new ExitStatus(0);
        $output = new Output($status, '', '');

        static::assertSame('', $output->stdout);
        static::assertSame('', $output->stderr);
    }
}
