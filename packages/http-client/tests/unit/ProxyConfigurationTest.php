<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\URL;

final class ProxyConfigurationTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $url = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($url);

        static::assertSame($url, $config->url);
        static::assertNull($config->authorization);
        static::assertNull($config->sni);
        static::assertSame([], $config->skipProxyFor);
    }

    public function testConstructorWithAllParameters(): void
    {
        $url = URL\parse('https://proxy:443');
        $config = new ProxyConfiguration(
            $url,
            'Basic dXNlcjpwYXNz',
            'proxy.example.com',
            ['localhost', '.internal.com'],
        );

        static::assertSame($url, $config->url);
        static::assertSame('Basic dXNlcjpwYXNz', $config->authorization);
        static::assertSame('proxy.example.com', $config->sni);
        static::assertSame(['localhost', '.internal.com'], $config->skipProxyFor);
    }

    public function testWithUrlReturnsNewInstance(): void
    {
        $originalUrl = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($originalUrl, 'Basic dXNlcjpwYXNz', 'proxy.example.com', ['localhost']);

        $newUrl = URL\parse('https://other-proxy:443');
        $new = $config->withUrl($newUrl);

        static::assertNotSame($config, $new);
        static::assertSame($newUrl, $new->url);
        static::assertSame($originalUrl, $config->url);
        static::assertSame('Basic dXNlcjpwYXNz', $new->authorization);
        static::assertSame('proxy.example.com', $new->sni);
        static::assertSame(['localhost'], $new->skipProxyFor);
    }

    public function testWithAuthorizationReturnsNewInstance(): void
    {
        $url = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($url, 'Basic dXNlcjpwYXNz', 'proxy.example.com', ['localhost']);

        $new = $config->withAuthorization('Bearer token123');

        static::assertNotSame($config, $new);
        static::assertSame('Bearer token123', $new->authorization);
        static::assertSame('Basic dXNlcjpwYXNz', $config->authorization);
        static::assertSame($url, $new->url);
        static::assertSame('proxy.example.com', $new->sni);
        static::assertSame(['localhost'], $new->skipProxyFor);
    }

    public function testWithAuthorizationNull(): void
    {
        $url = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($url, 'Basic dXNlcjpwYXNz');

        $new = $config->withAuthorization(null);

        static::assertNull($new->authorization);
        static::assertSame('Basic dXNlcjpwYXNz', $config->authorization);
    }

    public function testWithSniReturnsNewInstance(): void
    {
        $url = URL\parse('https://proxy:443');
        $config = new ProxyConfiguration($url, 'Basic dXNlcjpwYXNz', 'proxy.example.com', ['localhost']);

        $new = $config->withSni('other.example.com');

        static::assertNotSame($config, $new);
        static::assertSame('other.example.com', $new->sni);
        static::assertSame('proxy.example.com', $config->sni);
        static::assertSame($url, $new->url);
        static::assertSame('Basic dXNlcjpwYXNz', $new->authorization);
        static::assertSame(['localhost'], $new->skipProxyFor);
    }

    public function testWithSniNull(): void
    {
        $url = URL\parse('https://proxy:443');
        $config = new ProxyConfiguration($url, sni: 'proxy.example.com');

        $new = $config->withSni(null);

        static::assertNull($new->sni);
        static::assertSame('proxy.example.com', $config->sni);
    }

    public function testWithSkipProxyForReturnsNewInstance(): void
    {
        $url = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($url, 'Basic dXNlcjpwYXNz', 'proxy.example.com', ['localhost']);

        $new = $config->withSkipProxyFor(['*.internal.com', 'api.local']);

        static::assertNotSame($config, $new);
        static::assertSame(['*.internal.com', 'api.local'], $new->skipProxyFor);
        static::assertSame(['localhost'], $config->skipProxyFor);
        static::assertSame($url, $new->url);
        static::assertSame('Basic dXNlcjpwYXNz', $new->authorization);
        static::assertSame('proxy.example.com', $new->sni);
    }

    public function testWithSkipProxyForEmptyArray(): void
    {
        $url = URL\parse('http://proxy:8080');
        $config = new ProxyConfiguration($url, skipProxyFor: ['localhost', '.internal.com']);

        $new = $config->withSkipProxyFor([]);

        static::assertSame([], $new->skipProxyFor);
        static::assertSame(['localhost', '.internal.com'], $config->skipProxyFor);
    }

    public function testWithChaining(): void
    {
        $url = URL\parse('http://proxy:8080');
        $newUrl = URL\parse('https://secure-proxy:443');

        $config = new ProxyConfiguration($url)
            ->withUrl($newUrl)
            ->withAuthorization('Bearer abc')
            ->withSni('secure-proxy.example.com')
            ->withSkipProxyFor(['localhost', '.internal.com']);

        static::assertSame($newUrl, $config->url);
        static::assertSame('Bearer abc', $config->authorization);
        static::assertSame('secure-proxy.example.com', $config->sni);
        static::assertSame(['localhost', '.internal.com'], $config->skipProxyFor);
    }
}
