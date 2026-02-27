<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TLS;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\TCP;
use Psl\TLS;

final class LazyAcceptorTest extends TestCase
{
    /**
     * @var array{cert_file: string, key_file: string}|null
     */
    private static null|array $certFiles = null;

    public static function setUpBeforeClass(): void
    {
        if (!\extension_loaded('openssl')) {
            static::markTestSkipped('OpenSSL extension is required for TLS tests.');
        }

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $csr = openssl_csr_new([
            'commonName' => 'localhost',
            'organizationName' => 'PSL Test',
        ], $key);

        $cert = openssl_csr_sign($csr, null, $key, 1);

        $cert_file = tempnam(sys_get_temp_dir(), 'psl_tls_cert_');
        $key_file = tempnam(sys_get_temp_dir(), 'psl_tls_key_');

        openssl_x509_export_to_file($cert, $cert_file);
        openssl_pkey_export_to_file($key, $key_file);

        self::$certFiles = ['cert_file' => $cert_file, 'key_file' => $key_file];
    }

    public static function tearDownAfterClass(): void
    {
        if (null !== self::$certFiles) {
            @unlink(self::$certFiles['cert_file']);
            @unlink(self::$certFiles['key_file']);
            self::$certFiles = null;
        }
    }

    public function testLazyAcceptorInspectsClientHelloAndCompletes(): void
    {
        $cert = TLS\Certificate::create(self::$certFiles['cert_file'], self::$certFiles['key_file']);
        $serverConfig = TLS\ServerConfig::create($cert);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $serverConfig): void {
                $connection = $listener->accept();
                $lazy = TLS\LazyAcceptor::default();
                $hello = $lazy->accept($connection);

                // ClientHello should have server name from the client's SNI
                static::assertSame('localhost', $hello->getServerName());

                $tls = $hello->complete($serverConfig);
                static::assertInstanceOf(TLS\StreamInterface::class, $tls);

                $data = $tls->read();
                static::assertSame('lazy-hello', $data);
                $tls->writeAll('lazy-response');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfig::default()->withPeerVerification(false)->withAllowSelfSigned(true),
                );
                $client = $connector->connect($stream, 'localhost');

                $client->writeAll('lazy-hello');
                $response = $client->readAll();
                static::assertSame('lazy-response', $response);
                $client->close();
            },
        ]);
    }

    public function testLazyAcceptorWithAlpnProtocols(): void
    {
        $cert = TLS\Certificate::create(self::$certFiles['cert_file'], self::$certFiles['key_file']);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $cert): void {
                $connection = $listener->accept();
                $lazy = TLS\LazyAcceptor::default();
                $hello = $lazy->accept($connection);

                $alpn = $hello->getAlpnProtocols();
                static::assertNotNull($alpn);
                static::assertContains('h2', $alpn);
                static::assertContains('http/1.1', $alpn);

                $config = TLS\ServerConfig::create($cert)->withAlpnProtocols(['h2', 'http/1.1']);

                $tls = $hello->complete($config);

                $state = $tls->getState();
                static::assertSame('h2', $state->alpnProtocol);

                $tls->writeAll('ok');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfig::default()
                        ->withPeerVerification(false)
                        ->withAllowSelfSigned(true)
                        ->withAlpnProtocols(['h2', 'http/1.1']),
                );
                $client = $connector->connect($stream, 'localhost');

                static::assertSame('h2', $client->getState()->alpnProtocol);

                $data = $client->readAll();
                static::assertSame('ok', $data);
                $client->close();
            },
        ]);
    }

    public function testDefault(): void
    {
        $lazy = TLS\LazyAcceptor::default();

        static::assertInstanceOf(TLS\LazyAcceptor::class, $lazy);
    }
}
