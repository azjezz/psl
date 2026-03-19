<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Exception\DecodingException;

final class DecodingExceptionTest extends TestCase
{
    public function testForInvalidHuffmanPadding(): void
    {
        $exception = DecodingException::forInvalidHuffmanPadding();

        static::assertStringContainsString('padding', $exception->getMessage());
    }

    public function testForEosInHuffmanData(): void
    {
        $exception = DecodingException::forEosInHuffmanData();

        static::assertStringContainsString('EOS', $exception->getMessage());
    }

    public function testForIncompleteHuffmanSequence(): void
    {
        $exception = DecodingException::forIncompleteHuffmanSequence();

        static::assertStringContainsString('Incomplete', $exception->getMessage());
    }

    public function testForTableSizeUpdateNotAtBlockStart(): void
    {
        $exception = DecodingException::forTableSizeUpdateNotAtBlockStart();

        static::assertStringContainsString('start of a header block', $exception->getMessage());
    }

    public function testForUnexpectedEndOfData(): void
    {
        $exception = DecodingException::forUnexpectedEndOfData();

        static::assertStringContainsString('Unexpected end', $exception->getMessage());
    }

    public function testForInvalidStringLength(): void
    {
        $exception = DecodingException::forInvalidStringLength();

        static::assertStringContainsString('String length', $exception->getMessage());
    }

    public function testForTableSizeExceedsLimit(): void
    {
        $exception = DecodingException::forTableSizeExceedsLimit();

        static::assertStringContainsString('exceeds the protocol limit', $exception->getMessage());
    }

    public function testForTooManyTableSizeUpdates(): void
    {
        $exception = DecodingException::forTooManyTableSizeUpdates();

        static::assertStringContainsString('Too many', $exception->getMessage());
    }
}
