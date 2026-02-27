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
        $content_type = ord($data[0]);
        if ($content_type !== 22) {
            // Not a Handshake record
            return null;
        }

        // Handshake Header — offset 5 is safe since $len >= 43
        $handshake_type = ord($data[5]);
        if ($handshake_type !== 1) {
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

        $session_id_len = ord($data[$offset]);
        $offset += 1 + $session_id_len;

        if ($offset > $len) {
            return null;
        }

        // Cipher Suites (2-byte length prefix)
        if (($offset + 2) > $len) {
            return null;
        }

        /** @var array{1: int} $unpacked */
        $unpacked = unpack('n', $data, $offset);
        $cipher_suites_len = $unpacked[1];
        $offset += 2 + $cipher_suites_len;

        if ($offset > $len) {
            return null;
        }

        // Compression Methods (1-byte length prefix)
        if (($offset + 1) > $len) {
            return null;
        }

        $compression_len = ord($data[$offset]);
        $offset += 1 + $compression_len;

        if ($offset > $len) {
            return null;
        }

        // Extensions (2-byte length prefix)
        if (($offset + 2) > $len) {
            // No extensions — valid but no SNI/ALPN
            return ['server_name' => null, 'alpn_protocols' => null];
        }

        /** @var array{1: int} $unpacked */
        $unpacked = unpack('n', $data, $offset);
        $extensions_len = $unpacked[1];
        $offset += 2;

        $extensions_end = $offset + $extensions_len;
        if ($extensions_end > $len) {
            $extensions_end = $len;
        }

        $server_name = null;
        $alpn_protocols = null;

        while (($offset + 4) <= $extensions_end) {
            /** @var array{1: int, 2: int} $unpacked */
            $unpacked = unpack('n2', $data, $offset);
            $ext_type = $unpacked[1];
            $ext_len = $unpacked[2];
            $offset += 4;

            $ext_end = $offset + $ext_len;
            if ($ext_end > $extensions_end) {
                break;
            }

            if ($ext_type === 0 && $server_name === null) {
                // SNI extension
                $server_name = self::parseSni($data, $offset, $ext_end);
            }

            if ($ext_type === 16 && $alpn_protocols === null) {
                // ALPN extension
                $alpn_protocols = self::parseAlpn($data, $offset, $ext_end);
            }

            if ($server_name !== null && $alpn_protocols !== null) {
                break;
            }

            $offset = $ext_end;
        }

        return ['server_name' => $server_name, 'alpn_protocols' => $alpn_protocols];
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
            $name_type = ord($data[$offset]);
            $offset += 1;

            /** @var array{1: int} $unpacked */
            $unpacked = unpack('n', $data, $offset);
            $name_len = $unpacked[1];
            $offset += 2;

            if (($offset + $name_len) > $end) {
                return null;
            }

            if ($name_type === 0 && $name_len > 0) {
                // host_name type
                /** @var non-empty-string */
                return substr($data, $offset, $name_len);
            }

            $offset += $name_len;
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
            $proto_len = ord($data[$offset]);
            $offset += 1;

            if (($offset + $proto_len) > $end || $proto_len === 0) {
                break;
            }

            /** @var non-empty-string $proto */
            $proto = substr($data, $offset, $proto_len);
            $protocols[] = $proto;
            $offset += $proto_len;
        }

        return $protocols !== [] ? $protocols : null;
    }
}
