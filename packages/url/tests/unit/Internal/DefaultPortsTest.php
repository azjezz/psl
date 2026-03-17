<?php

declare(strict_types=1);

namespace Psl\URL\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\URL\Internal;

final class DefaultPortsTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function knownSchemeProvider(): iterable
    {
        yield 'http' => ['http', 80];
        yield 'https' => ['https', 443];
        yield 'ws' => ['ws', 80];
        yield 'wss' => ['wss', 443];
        yield 'ftp' => ['ftp', 21];
        yield 'ftps' => ['ftps', 990];
        yield 'ssh' => ['ssh', 22];
        yield 'sftp' => ['sftp', 22];
        yield 'ldap' => ['ldap', 389];
        yield 'ldaps' => ['ldaps', 636];
        yield 'redis' => ['redis', 6379];
        yield 'rediss' => ['rediss', 6380];
        yield 'mysql' => ['mysql', 3306];
        yield 'postgres' => ['postgres', 5432];
        yield 'amqp' => ['amqp', 5672];
        yield 'amqps' => ['amqps', 5671];
        yield 'mqtt' => ['mqtt', 1883];
        yield 'mqtts' => ['mqtts', 8883];
        yield 'git' => ['git', 9418];
        yield 'telnet' => ['telnet', 23];
        yield 'dns' => ['dns', 53];
    }

    #[DataProvider('knownSchemeProvider')]
    public function testKnownScheme(string $scheme, int $expectedPort): void
    {
        static::assertSame($expectedPort, Internal\default_port($scheme));
    }

    public function testUnknownScheme(): void
    {
        static::assertNull(Internal\default_port('unknown'));
    }

    public function testEmptyScheme(): void
    {
        static::assertNull(Internal\default_port(''));
    }
}
