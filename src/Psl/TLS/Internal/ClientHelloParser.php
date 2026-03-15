<?php

declare(strict_types=1);

namespace Psl\TLS\Internal;

use function ord;
use function strlen;
use function substr;
use function unpack;

/**
 * Parses a TLS ClientHello message to extract SNI and ALPN extensions.
 *
 * This parser handles the minimum necessary to inspect the ClientHello before
 * completing the TLS handshake, enabling SNI-based certificate selection and
 * protocol detection.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ClientHelloParser
{
    /**
     * Parse a raw TLS record (starting from the TLS record header) to extract
     * the server name (SNI) and ALPN protocols from the ClientHello.
     *
     * @return array{server_name: ?non-empty-string, alpn_protocols: ?list<non-empty-string>}|null
     *    Returns null if the data is not a valid ClientHello.
     */
    public static function parse(string $data): null|array
    {
        $len = strlen($data);
        // Minimum: 5 (record header) + 4 (handshake header) + 34 (client hello minimum)
        if ($len < 43) {
            return null;
        }

        // TLS Record Header
        $contentType = ord($data[0]);
        if ($contentType !== 22) {
            // Not a Handshake record
            return null;
        }

        // Handshake Header - offset 5 is safe since $len >= 43
        $handshakeType = ord($data[5]);
        if ($handshakeType !== 1) {
            // Not a ClientHello
            return null;
        }

        // Skip: record header (5) + handshake type (1) + handshake length (3) + client version (2) + random (32)
        // All guaranteed to fit by the $len < 43 check above.
        $offset = 5 + 1 + 3 + 2 + 32;

        // Session ID (variable length, 1-byte length prefix)
        if (($offset + 1) > $len) {
            return null;
        }

        $sessionIdLen = ord($data[$offset]);
        $offset += 1 + $sessionIdLen;

        if ($offset > $len) {
            return null;
        }

        // Cipher Suites (2-byte length prefix)
        if (($offset + 2) > $len) {
            return null;
        }

        /** @var array{1: int} $unpacked */
        $unpacked = unpack('n', $data, $offset);
        $cipherSuitesLen = $unpacked[1];
        $offset += 2 + $cipherSuitesLen;

        if ($offset > $len) {
            return null;
        }

        // Compression Methods (1-byte length prefix)
        if (($offset + 1) > $len) {
            return null;
        }

        $compressionLen = ord($data[$offset]);
        $offset += 1 + $compressionLen;

        if ($offset > $len) {
            return null;
        }

        // Extensions (2-byte length prefix)
        if (($offset + 2) > $len) {
            // No extensions; valid but no SNI/ALPN
            return ['server_name' => null, 'alpn_protocols' => null];
        }

        /** @var array{1: int} $unpacked */
        $unpacked = unpack('n', $data, $offset);
        $extensionsLen = $unpacked[1];
        $offset += 2;

        $extensionsEnd = $offset + $extensionsLen;
        if ($extensionsEnd > $len) {
            $extensionsEnd = $len;
        }

        $serverName = null;
        $alpnProtocols = null;

        while (($offset + 4) <= $extensionsEnd) {
            /** @var array{1: int, 2: int} $unpacked */
            $unpacked = unpack('n2', $data, $offset);
            $extType = $unpacked[1];
            $extLen = $unpacked[2];
            $offset += 4;

            $extEnd = $offset + $extLen;
            if ($extEnd > $extensionsEnd) {
                break;
            }

            if ($extType === 0 && $serverName === null) {
                // SNI extension
                $serverName = self::parseSni($data, $offset, $extEnd);
            }

            if ($extType === 16 && $alpnProtocols === null) {
                // ALPN extension
                $alpnProtocols = self::parseAlpn($data, $offset, $extEnd);
            }

            if ($serverName !== null && $alpnProtocols !== null) {
                break;
            }

            $offset = $extEnd;
        }

        return ['server_name' => $serverName, 'alpn_protocols' => $alpnProtocols];
    }

    /**
     * Parse the SNI extension data.
     *
     * @return non-empty-string|null
     */
    private static function parseSni(string $data, int $offset, int $end): null|string
    {
        // Server Name List length (2 bytes)
        if (($offset + 2) > $end) {
            return null;
        }

        $offset += 2; // skip list length

        while (($offset + 3) <= $end) {
            $nameType = ord($data[$offset]);
            $offset += 1;

            /** @var array{1: int} $unpacked */
            $unpacked = unpack('n', $data, $offset);
            $nameLen = $unpacked[1];
            $offset += 2;

            if (($offset + $nameLen) > $end) {
                return null;
            }

            if ($nameType === 0 && $nameLen > 0) {
                // host_name type
                /** @var non-empty-string */
                return substr($data, $offset, $nameLen);
            }

            $offset += $nameLen;
        }

        return null;
    }

    /**
     * Parse the ALPN extension data.
     *
     * @return list<non-empty-string>|null
     */
    private static function parseAlpn(string $data, int $offset, int $end): null|array
    {
        // ALPN Protocol Name List length (2 bytes)
        if (($offset + 2) > $end) {
            return null;
        }

        $offset += 2; // skip list length

        $protocols = [];
        while (($offset + 1) <= $end) {
            $protoLen = ord($data[$offset]);
            $offset += 1;

            if (($offset + $protoLen) > $end || $protoLen === 0) {
                break;
            }

            /** @var non-empty-string $proto */
            $proto = substr($data, $offset, $protoLen);
            $protocols[] = $proto;
            $offset += $protoLen;
        }

        return $protocols !== [] ? $protocols : null;
    }
}
