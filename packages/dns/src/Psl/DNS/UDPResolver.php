<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Internal\Decoder;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\RecordType;
use Psl\IO;
use Psl\Network;
use Psl\UDP;

/**
 * DNS resolver that sends queries over UDP.
 *
 * Throws {@see Exception\ProtocolException} when the response has the
 * TC flag set, allowing a {@see FallbackResolver} to fall through to a
 * TCP-based resolver.
 *
 * @api
 */
final readonly class UDPResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param non-empty-string $host The nameserver hostname or IP address.
     * @param int<0, 65535> $port The nameserver port.
     * @param bool $dnssec Whether to set the DNSSEC OK (DO) flag in queries.
     * @param int $udpPayloadSize The maximum UDP payload size advertised via EDNS0.
     */
    public function __construct(
        private string $host,
        private int $port = 53,
        private bool $dnssec = false,
        private int $udpPayloadSize = 1232,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        [$id, $packet] = Encoder::encode($name, $type, $this->dnssec, $this->udpPayloadSize, $ednsOptions);

        try {
            $socket = UDP\Socket::bind();
            try {
                $address = Network\Address::udp($this->host, $this->port);
                $socket->sendTo($packet, $address, $cancellation);
                /** @var positive-int $bufferSize */
                $bufferSize = $this->udpPayloadSize;
                [$responseData, $peer] = $socket->receiveFrom($bufferSize, $cancellation);
            } finally {
                $socket->close();
            }
        } catch (Network\Exception\InvalidArgumentException $e) {
            throw Exception\NetworkException::forInvalidServerAddress($e->getMessage(), $e);
        } catch (IO\Exception\AlreadyClosedException $e) {
            throw Exception\NetworkException::forSocketClosed($e);
        } catch (Network\Exception\RuntimeException $e) {
            throw Exception\NetworkException::forQueryFailed('UDP', $e->getMessage(), $e);
        }

        if ($peer->host !== $this->host || $peer->port !== $this->port) {
            throw Exception\ProtocolException::forUnexpectedSource(
                $this->host . ':' . $this->port,
                $peer->host . ':' . ($peer->port ?? 0),
            );
        }

        $response = Decoder::decode($responseData);
        if ($response->id !== $id) {
            throw Exception\ProtocolException::forTransactionIDMismatch($id, $response->id);
        }

        if (Decoder::isTruncated($responseData)) {
            throw Exception\ProtocolException::forUDPResponse();
        }

        return $response;
    }
}
