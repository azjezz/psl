<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use Psl\H2\Exception\ProtocolException;
use Psl\HPACK\Header;

use function strtolower;

/**
 * Validates HTTP/2 header fields per RFC 9113 §8.2–§8.3 and RFC 8441.
 *
 * @internal
 */
final readonly class HeaderValidator
{
    /**
     * HTTP/1.1 connection-specific headers that are forbidden in HTTP/2 (RFC 9113 section 8.2.2).
     *
     * @var array<string, true>
     */
    private const array BANNED_HEADERS = [
        'connection' => true,
        'keep-alive' => true,
        'proxy-connection' => true,
        'upgrade' => true,
        'transfer-encoding' => true,
    ];

    /**
     * Valid request pseudo-headers per RFC 9113 Section 8.3.1 and RFC 8441.
     *
     * @var array<string, true>
     */
    private const array REQUEST_PSEUDO_HEADERS = [
        ':method' => true,
        ':scheme' => true,
        ':path' => true,
        ':authority' => true,
        ':protocol' => true,
    ];

    /**
     * Valid response pseudo-headers per RFC 9113 Section 8.3.2.
     *
     * @var array<string, true>
     */
    private const array RESPONSE_PSEUDO_HEADERS = [
        ':status' => true,
    ];

    /**
     * @param list<Header> $headers
     *
     * @throws ProtocolException If headers violate RFC 9113 §8.2–§8.3.
     */
    public static function validate(array $headers, bool $isClient, bool $isTrailing = false): void
    {
        $pseudoSeen = [];
        $pseudosDone = false;
        $method = null;
        $hasProtocol = false;

        foreach ($headers as $header) {
            if ($header->name[0] === ':') {
                if ($isTrailing) {
                    throw ProtocolException::forConnectionError(
                        'Pseudo-header "' . $header->name . '" in trailing headers',
                    );
                }

                if ($pseudosDone) {
                    throw ProtocolException::forConnectionError(
                        'Pseudo-header "' . $header->name . '" appears after regular headers',
                    );
                }

                if (isset($pseudoSeen[$header->name])) {
                    throw ProtocolException::forConnectionError('Duplicate pseudo-header "' . $header->name . '"');
                }

                $allowed = $isClient ? self::RESPONSE_PSEUDO_HEADERS : self::REQUEST_PSEUDO_HEADERS;
                if (!isset($allowed[$header->name])) {
                    throw ProtocolException::forConnectionError(
                        'Invalid pseudo-header "'
                        . $header->name
                        . '" for '
                        . ($isClient ? 'client' : 'server')
                        . ' role',
                    );
                }

                $pseudoSeen[$header->name] = true;

                if ($header->name === ':method') {
                    $method = $header->value;
                }

                if ($header->name === ':protocol') {
                    $hasProtocol = true;
                }

                if ($header->name === ':path' && $header->value === '') {
                    throw ProtocolException::forConnectionError('Pseudo-header ":path" must not be empty');
                }
            } else {
                $pseudosDone = true;

                $lower = strtolower($header->name);
                if ($lower !== $header->name) {
                    throw ProtocolException::forConnectionError(
                        'Header name "' . $header->name . '" contains uppercase characters',
                    );
                }

                if ($lower === 'te' && $header->value !== 'trailers') {
                    throw ProtocolException::forConnectionError('Header "te" has value other than "trailers"');
                }

                if (isset(self::BANNED_HEADERS[$lower])) {
                    throw ProtocolException::forConnectionError('Banned header "' . $header->name . '" in HTTP/2');
                }
            }
        }

        if (!$isTrailing) {
            if ($isClient) {
                self::validateResponsePseudoHeaders($pseudoSeen);
            } else {
                self::validateRequestPseudoHeaders($pseudoSeen, $method, $hasProtocol);
            }
        }
    }

    /**
     * @param array<string, true> $pseudoSeen
     *
     * @throws ProtocolException
     */
    private static function validateRequestPseudoHeaders(
        array $pseudoSeen,
        null|string $method,
        bool $hasProtocol,
    ): void {
        if (!isset($pseudoSeen[':method'])) {
            throw ProtocolException::forConnectionError('Missing required pseudo-header ":method"');
        }

        if ($method === 'CONNECT') {
            if ($hasProtocol) {
                // Extended CONNECT (RFC 8441): :scheme and :path are REQUIRED.
                if (!isset($pseudoSeen[':scheme'])) {
                    throw ProtocolException::forConnectionError('Extended CONNECT request MUST include ":scheme"');
                }

                if (!isset($pseudoSeen[':path'])) {
                    throw ProtocolException::forConnectionError('Extended CONNECT request MUST include ":path"');
                }
            } else {
                // Regular CONNECT (RFC 9113 §8.5): :scheme and :path MUST NOT be present.
                if (isset($pseudoSeen[':scheme'])) {
                    throw ProtocolException::forConnectionError('CONNECT request MUST NOT include ":scheme"');
                }

                if (isset($pseudoSeen[':path'])) {
                    throw ProtocolException::forConnectionError('CONNECT request MUST NOT include ":path"');
                }
            }
        } else {
            // :protocol is only valid with CONNECT
            if ($hasProtocol) {
                throw ProtocolException::forConnectionError(
                    ':protocol pseudo-header is only valid with the CONNECT method',
                );
            }

            if (!isset($pseudoSeen[':scheme'])) {
                throw ProtocolException::forConnectionError('Missing required pseudo-header ":scheme"');
            }

            if (!isset($pseudoSeen[':path'])) {
                throw ProtocolException::forConnectionError('Missing required pseudo-header ":path"');
            }
        }
    }

    /**
     * @param array<string, true> $pseudoSeen
     *
     * @throws ProtocolException
     */
    private static function validateResponsePseudoHeaders(array $pseudoSeen): void
    {
        if (!isset($pseudoSeen[':status'])) {
            throw ProtocolException::forConnectionError('Missing required pseudo-header ":status"');
        }
    }
}
