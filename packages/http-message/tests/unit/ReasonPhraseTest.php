<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Message;

final class ReasonPhraseTest extends TestCase
{
    public function testOk(): void
    {
        static::assertSame('OK', Message\reason_phrase(200));
    }

    public function testNotFound(): void
    {
        static::assertSame('Not Found', Message\reason_phrase(404));
    }

    public function testInternalServerError(): void
    {
        static::assertSame('Internal Server Error', Message\reason_phrase(500));
    }

    public function testContinue(): void
    {
        static::assertSame('Continue', Message\reason_phrase(100));
    }

    public function testTeapot(): void
    {
        static::assertSame("I'm a Teapot", Message\reason_phrase(418));
    }

    public function testMovedPermanently(): void
    {
        static::assertSame('Moved Permanently', Message\reason_phrase(301));
    }

    public function testUnknownCode(): void
    {
        static::assertSame('status code 999', Message\reason_phrase(999));
    }

    public function testUnknownCodeInRange(): void
    {
        static::assertSame('status code 620', Message\reason_phrase(620));
    }
}
