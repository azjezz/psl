<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Shell;

use PHPUnit\Framework\TestCase;
use Psl\Shell;
use Psl\Str;

use function pack;

use const PHP_BINARY;

final class UnpackTest extends TestCase
{
    public function testUnpacking(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );

        [$stdout, $stderr] = Shell\unpack($result);

        static::assertSame('hello', $stdout);
        static::assertSame(' world', $stderr);
    }

    public function testUnpackingStandardOutputOnly(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );

        [$stdout, $stderr] = Shell\unpack($result);

        static::assertSame('hello', $stdout);
        static::assertSame('', $stderr);
    }

    public function testUnpackingStandardErrorOutputOnly(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );

        [$stdout, $stderr] = Shell\unpack($result);

        static::assertSame('hello', $stderr);
        static::assertSame('', $stdout);
    }

    public function testUnpackingEmpty(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'exit(0);'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );

        [$stdout, $stderr] = Shell\unpack($result);

        static::assertSame('', $stderr);
        static::assertSame('', $stdout);
    }

    public function testUnpackingInvalidMessage(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );
        $result .= ' world!';

        $this->expectException(Shell\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$content contains an invalid header value.');

        Shell\unpack($result);
    }

    public function testUnpackingInvalidAdditionalHeader(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );
        $result .= 'x';

        $this->expectException(Shell\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$content contains an invalid header value.');

        Shell\unpack($result);
    }

    public function testUnpackingInvalidNulHeader(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );
        $result .= "\0\0\0\0\0";

        $this->expectException(Shell\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$content contains an invalid header value.');

        Shell\unpack($result);
    }

    public function testUnpackingInvalidLength(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );
        $result .= Str\slice($result, 0, 7);

        $this->expectException(Shell\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$content contains an invalid header value.');

        Shell\unpack($result);
    }

    public function testUnpackingInvalidType(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDERR, "hello");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );
        $result .= pack('C1N1', 3, 1) . 'a';

        $this->expectException(Shell\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$content contains an invalid header value.');

        Shell\unpack($result);
    }
}
