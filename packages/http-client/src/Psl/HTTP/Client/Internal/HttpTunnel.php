<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\TLS\ClientConfiguration;
use Psl\TLS\Connector;
use Psl\URL;

use function base64_encode;
use function preg_match;

/**
 * Establishes an HTTP CONNECT tunnel through an HTTP proxy.
 *
 * Connects to the tunnel address, sends an HTTP/1.1 CONNECT request for the
 * target host:port, and reads the proxy's response. On success (2xx status),
 * the underlying TCP stream becomes a transparent byte pipe to the target,
 * ready for TLS handshake or plaintext HTTP.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.6 CONNECT Method
 *
 * @internal
 */
final class HttpTunnel
{
    private function __construct() {}

    /**
     * Open a CONNECT tunnel to the target through the given tunnel proxy.
     *
     * @param non-empty-string $tunnelAddress The tunnel proxy address (e.g., "http://proxy:8080").
     * @param non-empty-string $targetHost The target hostname to tunnel to.
     * @param int<1, 65535> $targetPort The target port to tunnel to.
     *
     * @throws Network\Exception\RuntimeException If the TCP connection to the tunnel proxy fails.
     * @throws ProtocolException If the tunnel proxy returns a non-2xx response.
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the handshake.
     * @throws Async\Exception\CancelledException If the cancellation token fires.
     */
    public static function connect(
        TCP\ConnectorInterface $tcpConnector,
        string $tunnelAddress,
        string $targetHost,
        int $targetPort,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $tlsConfiguration,
    ): TCP\StreamInterface {
        try {
            $parsed = URL\parse($tunnelAddress);
        } catch (URL\Exception\InvalidURLException $e) {
            throw ProtocolException::forMalformedResponse('Invalid tunnel address: ' . $tunnelAddress);
        }

        $tunnelHost = $parsed->authority->host->toString();
        $tunnelPort = $parsed->authority->port ?? ($parsed->scheme === 'https' ? 443 : 80);
        $tunnelTls = $parsed->scheme === 'https';

        $stream = $tcpConnector->connect($tunnelHost, $tunnelPort, $cancellation);

        if ($tunnelTls) {
            $tlsConnector = new Connector($tlsConfiguration);
            $stream = $tlsConnector->connect($stream, $tunnelHost, $cancellation);
        }

        $authority = $targetHost . ':' . $targetPort;
        $connectRequest = "CONNECT {$authority} HTTP/1.1\r\nHost: {$authority}\r\n";

        $userInfo = $parsed->authority->userInfo;
        if ($userInfo !== null) {
            $encoded = base64_encode($userInfo);
            $connectRequest .= "Proxy-Authorization: Basic {$encoded}\r\n";
        }

        $connectRequest .= "\r\n";

        $stream->writeAll($connectRequest, $cancellation);

        $reader = new IO\Reader($stream);
        $statusLine = $reader->readLine($cancellation);
        if ($statusLine === null) {
            throw ProtocolException::forUnexpectedEndOfStream();
        }

        $matches = null;
        if (!preg_match('#^HTTP/1\.[01] (\d{3})#', $statusLine, $matches)) {
            throw ProtocolException::forMalformedResponse('Invalid CONNECT response: ' . $statusLine);
        }

        $statusCode = (int) $matches[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw ProtocolException::forMalformedResponse('CONNECT tunnel failed with status ' . $statusCode);
        }

        while (true) {
            $line = $reader->readLine($cancellation);
            if ($line === null || $line === '') {
                break;
            }
        }

        return $stream;
    }
}
