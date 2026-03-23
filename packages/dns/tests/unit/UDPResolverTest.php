<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DNS\UDPResolver;

final class UDPResolverTest extends TestCase
{
    public function testConstructorAcceptsHostAndPort(): void
    {
        $resolver = new UDPResolver(host: '127.0.0.1', port: 5353);

        static::assertInstanceOf(UDPResolver::class, $resolver);
    }
}
