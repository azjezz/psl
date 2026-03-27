<?php

declare(strict_types=1);

namespace Psl\URL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URL;
use Psl\URL\Exception\InvalidURLException;

final class ParseTest extends TestCase
{
    public function testStandardURL(): void
    {
        $url = URL\parse('https://example.com/path?q=1#f');

        static::assertSame('https', $url->scheme);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
        static::assertSame('/path', $url->path);
        static::assertSame('q=1', $url->query);
        static::assertSame('f', $url->fragment);
    }

    public function testDefaultPortStripped(): void
    {
        $url = URL\parse('http://example.com:80/');

        static::assertNull($url->authority->port);
    }

    public function testNonDefaultPortKept(): void
    {
        $url = URL\parse('http://example.com:8080/');

        static::assertSame(8080, $url->authority->port);
    }

    public function testHTTPSDefaultPortStripped(): void
    {
        $url = URL\parse('https://example.com:443/');

        static::assertNull($url->authority->port);
    }

    public function testIPv6URL(): void
    {
        $url = URL\parse('http://[::1]:8080/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame(8080, $url->authority->port);
        static::assertSame('/path', $url->path);
    }

    public function testWithUserInfo(): void
    {
        $url = URL\parse('http://user:pass@example.com/');

        static::assertSame('user:pass', $url->authority->userInfo);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
    }

    public function testMissingScheme(): void
    {
        $this->expectException(InvalidURLException::class);

        URL\parse('//example.com/path');
    }

    public function testMissingAuthority(): void
    {
        $this->expectException(InvalidURLException::class);

        URL\parse('mailto:user@host');
    }

    public function testRelativePath(): void
    {
        $this->expectException(InvalidURLException::class);

        URL\parse('example.com/path');
    }

    public function testWSDefaultPortStripped(): void
    {
        $url = URL\parse('ws://example.com:80/');

        static::assertNull($url->authority->port);
    }

    public function testWSSDefaultPortStripped(): void
    {
        $url = URL\parse('wss://example.com:443/');

        static::assertNull($url->authority->port);
    }

    public function testFTPDefaultPortStripped(): void
    {
        $url = URL\parse('ftp://example.com:21/');

        static::assertNull($url->authority->port);
    }

    public function testFTPSDefaultPortStripped(): void
    {
        $url = URL\parse('ftps://example.com:990/');

        static::assertNull($url->authority->port);
    }

    public function testSSHDefaultPortStripped(): void
    {
        $url = URL\parse('ssh://example.com:22/');

        static::assertNull($url->authority->port);
    }

    public function testSFTPDefaultPortStripped(): void
    {
        $url = URL\parse('sftp://example.com:22/');

        static::assertNull($url->authority->port);
    }

    public function testLDAPDefaultPortStripped(): void
    {
        $url = URL\parse('ldap://example.com:389/');

        static::assertNull($url->authority->port);
    }

    public function testLDAPSDefaultPortStripped(): void
    {
        $url = URL\parse('ldaps://example.com:636/');

        static::assertNull($url->authority->port);
    }

    public function testRedisDefaultPortStripped(): void
    {
        $url = URL\parse('redis://example.com:6379/');

        static::assertNull($url->authority->port);
    }

    public function testRedissDefaultPortStripped(): void
    {
        $url = URL\parse('rediss://example.com:6380/');

        static::assertNull($url->authority->port);
    }

    public function testMySQLDefaultPortStripped(): void
    {
        $url = URL\parse('mysql://example.com:3306/');

        static::assertNull($url->authority->port);
    }

    public function testPostgresDefaultPortStripped(): void
    {
        $url = URL\parse('postgres://example.com:5432/');

        static::assertNull($url->authority->port);
    }

    public function testAMQPDefaultPortStripped(): void
    {
        $url = URL\parse('amqp://example.com:5672/');

        static::assertNull($url->authority->port);
    }

    public function testAMQPSDefaultPortStripped(): void
    {
        $url = URL\parse('amqps://example.com:5671/');

        static::assertNull($url->authority->port);
    }

    public function testMQTTDefaultPortStripped(): void
    {
        $url = URL\parse('mqtt://example.com:1883/');

        static::assertNull($url->authority->port);
    }

    public function testMQTTSDefaultPortStripped(): void
    {
        $url = URL\parse('mqtts://example.com:8883/');

        static::assertNull($url->authority->port);
    }

    public function testGitDefaultPortStripped(): void
    {
        $url = URL\parse('git://example.com:9418/');

        static::assertNull($url->authority->port);
    }

    public function testTelnetDefaultPortStripped(): void
    {
        $url = URL\parse('telnet://example.com:23/');

        static::assertNull($url->authority->port);
    }

    public function testDNSDefaultPortStripped(): void
    {
        $url = URL\parse('dns://example.com:53/');

        static::assertNull($url->authority->port);
    }

    public function testFTPNonDefaultPortKept(): void
    {
        $url = URL\parse('ftp://example.com:2121/');

        static::assertSame(2121, $url->authority->port);
    }

    public function testSSHNonDefaultPortKept(): void
    {
        $url = URL\parse('ssh://example.com:2222/');

        static::assertSame(2222, $url->authority->port);
    }

    public function testRedisNonDefaultPortKept(): void
    {
        $url = URL\parse('redis://example.com:6380/');

        static::assertSame(6380, $url->authority->port);
    }

    public function testPostgresNonDefaultPortKept(): void
    {
        $url = URL\parse('postgres://example.com:15432/');

        static::assertSame(15_432, $url->authority->port);
    }

    public function testMySQLNonDefaultPortKept(): void
    {
        $url = URL\parse('mysql://example.com:33060/');

        static::assertSame(33_060, $url->authority->port);
    }

    public function testUnknownSchemePortRetained(): void
    {
        $url = URL\parse('custom://example.com:9999/');

        static::assertSame('custom', $url->scheme);
        static::assertSame(9999, $url->authority->port);
    }

    public function testURLWithQueryAndFragment(): void
    {
        $url = URL\parse('https://example.com/path?key=value&foo=bar#section');

        static::assertSame('https', $url->scheme);
        static::assertSame('/path', $url->path);
        static::assertSame('key=value&foo=bar', $url->query);
        static::assertSame('section', $url->fragment);
    }

    public function testURLWithEmptyPath(): void
    {
        $url = URL\parse('https://example.com');

        static::assertSame('https', $url->scheme);
        static::assertSame('', $url->path);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
    }

    public function testURLWithEmptyQuery(): void
    {
        $url = URL\parse('https://example.com/path?');

        static::assertSame('', $url->query);
    }

    public function testURLWithEmptyFragment(): void
    {
        $url = URL\parse('https://example.com/path#');

        static::assertSame('', $url->fragment);
    }

    public function testURLWithUserInfoUsernameOnly(): void
    {
        $url = URL\parse('http://admin@example.com/');

        static::assertSame('admin', $url->authority->userInfo);
    }

    public function testIPv6WithDefaultPortStripped(): void
    {
        $url = URL\parse('http://[::1]:80/');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertNull($url->authority->port);
    }

    public function testIPv6WithNonDefaultPort(): void
    {
        $url = URL\parse('https://[::1]:9443/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame(9443, $url->authority->port);
        static::assertSame('/path', $url->path);
    }

    public function testRootlessPathRejected(): void
    {
        $this->expectException(InvalidURLException::class);

        URL\parse('http:foo');
    }

    public function testSchemeNormalization(): void
    {
        $url = URL\parse('HTTP://example.com/');

        static::assertSame('http', $url->scheme);
    }

    public function testHostNormalization(): void
    {
        $url = URL\parse('http://EXAMPLE.COM/');

        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
    }

    public function testSchemeAndHostNormalizationCombined(): void
    {
        $url = URL\parse('HTTPS://WWW.EXAMPLE.COM:443/Path?Query=1#Frag');

        static::assertSame('https', $url->scheme);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('www.example.com', $url->authority->host->name);
        static::assertNull($url->authority->port);
        static::assertSame('/Path', $url->path);
        static::assertSame('Query=1', $url->query);
        static::assertSame('Frag', $url->fragment);
    }

    public function testBareIPv6NoPort(): void
    {
        $url = URL\parse('http://::1/path');

        static::assertSame('http', $url->scheme);
        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('::1', $url->authority->host->address->toString());
        static::assertNull($url->authority->port);
        static::assertSame('/path', $url->path);
    }

    public function testBareIPv6FullAddress(): void
    {
        $url = URL\parse('http://2001:db8::1/path');

        static::assertSame('http', $url->scheme);
        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('2001:db8::1', $url->authority->host->address->toString());
        static::assertNull($url->authority->port);
    }

    public function testBareIPv6Loopback(): void
    {
        $url = URL\parse('https://::1/');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('::1', $url->authority->host->address->toString());
        static::assertNull($url->authority->port);
    }

    public function testBareIPv6MappedIPv4(): void
    {
        $url = URL\parse('http://::ffff:192.168.1.1/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('::ffff:192.168.1.1', $url->authority->host->address->toString());
        static::assertNull($url->authority->port);
    }

    public function testBracketedIPv6WithPort(): void
    {
        $url = URL\parse('http://[::1]:8080/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('::1', $url->authority->host->address->toString());
        static::assertSame(8080, $url->authority->port);
        static::assertSame('/path', $url->path);
    }

    public function testBracketedIPv6WithoutPort(): void
    {
        $url = URL\parse('http://[::1]/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('::1', $url->authority->host->address->toString());
        static::assertNull($url->authority->port);
    }

    public function testBracketedIPv6WithZoneId(): void
    {
        $url = URL\parse('http://[fe80::1%25eth0]:9090/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('fe80::1', $url->authority->host->address->toString());
        static::assertSame('eth0', $url->authority->host->zone);
        static::assertSame(9090, $url->authority->port);
    }

    public function testBareIPv6AmbiguousWithPortParsesAsIPv6(): void
    {
        $url = URL\parse('http://::1:8080/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertNull($url->authority->port);
    }

    public function testIPv4StillWorks(): void
    {
        $url = URL\parse('http://127.0.0.1:8080/path');

        static::assertInstanceOf(IPHost::class, $url->authority->host);
        static::assertSame('127.0.0.1', $url->authority->host->address->toString());
        static::assertSame(8080, $url->authority->port);
    }

    public function testRegularHostnameWithPortStillWorks(): void
    {
        $url = URL\parse('http://example.com:443/path');

        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
        static::assertSame(443, $url->authority->port);
    }
}
