<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Internal\Decoder;
use Psl\DNS\Internal\Encoder;
use Psl\DNS\Record\RecordType;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

use function strlen;

/**
 * DNS resolver that sends queries over HTTPS (DNS-over-HTTPS, RFC 8484).
 *
 * Queries are encoded as binary DNS wire-format packets and POSTed to the
 * configured endpoint with `application/dns-message` content type.
 * The response is decoded from the same binary wire format.
 *
 * HTTP/2 multiplexing allows concurrent queries to share a single connection.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8484
 *
 * @api
 */
final readonly class HTTPSResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    private Client\ClientInterface $client;
    private URL\URL $url;

    /**
     * @param non-empty-string $url The DoH endpoint URL (e.g., "https://1.1.1.1/dns-query").
     * @param null|Client\ClientInterface $client HTTP client instance; a default client is created if null.
     * @param bool $dnssec Whether to set the DNSSEC OK (DO) flag in queries.
     */
    public function __construct(
        string $url,
        null|Client\ClientInterface $client = null,
        private bool $dnssec = false,
    ) {
        $this->url = URL\parse($url);
        $this->client = $client ?? new Client\Client();
    }

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        [$id, $packet] = Encoder::encode($name, $type, $this->dnssec, ednsOptions: $ednsOptions);

        try {
            $tx = $this->client->send(
                new Message\Request(
                    method: Message\METHOD_POST,
                    url: $this->url,
                    headers: Message\FieldMap::from([
                        ['content-type', 'application/dns-message'],
                        ['accept', 'application/dns-message'],
                        ['content-length', (string) strlen($packet)],
                    ]),
                    body: new IO\MemoryHandle($packet),
                ),
                cancellation: $cancellation,
            );
        } catch (Client\Exception\ExceptionInterface $e) {
            throw Exception\NetworkException::forQueryFailed('HTTPS', $e->getMessage(), $e);
        }

        if ($tx->response->status !== Message\STATUS_OK) {
            throw Exception\NetworkException::forQueryFailed('HTTPS', 'server returned HTTP ' . $tx->response->status);
        }

        $responseData = $tx->response->body?->readAll() ?? '';

        $response = Decoder::decode($responseData);
        if ($response->id !== $id) {
            throw Exception\ProtocolException::forTransactionIDMismatch($id, $response->id);
        }

        return $response;
    }
}
