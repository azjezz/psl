<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TLS;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Str;
use Psl\TCP;
use Psl\TLS;

final class ConnectTest extends TestCase
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

    public function testTlsClientServer(): void
    {
        $cert = TLS\Certificate::create(self::$certFiles['cert_file'], self::$certFiles['key_file']);
        $server_config = TLS\ServerConfig::create($cert);
        $acceptor = new TLS\Acceptor($server_config);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $acceptor): void {
                $connection = $listener->accept();
                $tls = $acceptor->accept($connection);
                self::assertInstanceOf(TLS\StreamInterface::class, $tls);
                $request = $tls->read();
                self::assertSame('Hello, TLS!', $request);
                $tls->writeAll(Str\reverse($request));
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfig::default()->withPeerVerification(false)->withAllowSelfSigned(true),
                );
                $client = $connector->connect($stream, 'localhost');

                self::assertInstanceOf(TLS\StreamInterface::class, $client);

                $state = $client->getState();
                self::assertNotEmpty($state->cipherName);
                self::assertGreaterThan(0, $state->cipherBits);

                $client->writeAll('Hello, TLS!');
                $response = $client->readAll();
                self::assertSame('!SLT ,olleH', $response);
                $client->close();
            },
        ]);
    }

    public function testTlsWithMinimumVersion(): void
    {
        $cert = TLS\Certificate::create(self::$certFiles['cert_file'], self::$certFiles['key_file']);
        $server_config = TLS\ServerConfig::create($cert)->withMinimumVersion(TLS\Version::Tls12);
        $acceptor = new TLS\Acceptor($server_config);

        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        Async\concurrently([
            'server' => static function () use ($listener, $acceptor): void {
                $connection = $listener->accept();
                $tls = $acceptor->accept($connection);
                self::assertInstanceOf(TLS\StreamInterface::class, $tls);

                $state = $tls->getState();
                self::assertTrue($state->version === TLS\Version::Tls12 || $state->version === TLS\Version::Tls13);

                $data = $tls->read();
                self::assertSame('ping', $data);
                $tls->writeAll('pong');
                $tls->close();
                $listener->close();
            },
            'client' => static function () use ($port): void {
                $stream = TCP\connect('127.0.0.1', $port);
                $connector = new TLS\Connector(
                    TLS\ClientConfig::default()
                        ->withPeerVerification(false)
                        ->withAllowSelfSigned(true)
                        ->withMinimumVersion(TLS\Version::Tls12),
                );
                $client = $connector->connect($stream, 'localhost');

                self::assertInstanceOf(TLS\StreamInterface::class, $client);

                $state = $client->getState();
                self::assertTrue($state->version === TLS\Version::Tls12 || $state->version === TLS\Version::Tls13);
                self::assertNotEmpty($state->cipherName);
                self::assertNull($state->alpnProtocol);

                $client->writeAll('ping');
                $response = $client->readAll();
                self::assertSame('pong', $response);
                $client->close();
            },
        ]);
    }
}
