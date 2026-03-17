<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI\Authority\RegisteredNameHost;

final class RegisteredNameHostTest extends TestCase
{
    public function testExampleDotCom(): void
    {
        $host = new RegisteredNameHost(name: 'example.com');

        static::assertSame('example.com', $host->toString());
    }

    public function testLocalhost(): void
    {
        $host = new RegisteredNameHost(name: 'localhost');

        static::assertSame('localhost', $host->toString());
    }

    public function testStringable(): void
    {
        $host = new RegisteredNameHost(name: 'example.com');

        static::assertSame('example.com', (string) $host);
    }
}
