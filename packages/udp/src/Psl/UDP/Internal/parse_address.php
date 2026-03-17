<?php

declare(strict_types=1);

namespace Psl\UDP\Internal;

use Psl\Network;

use function str_starts_with;
use function strlen;
use function strpos;
use function strrpos;
use function substr;

/**
 * Parse a "host:port" or "[host]:port" string into an Address.
 *
 * Handles both IPv4 ("127.0.0.1:8080") and IPv6 ("[::1]:8080") formats.
 *
 * @internal
 *
 * @throws Network\Exception\RuntimeException If the address contains an invalid port.
 */
function parse_address(string $address): Network\Address
{
    if ($address === '') {
        return Network\Address::udp('0.0.0.0', 0);
    }

    // IPv6 bracket notation: [host]:port
    if (str_starts_with($address, '[')) {
        $closeBracket = strpos($address, ']');
        if ($closeBracket === false) {
            return Network\Address::udp($address, 0);
        }

        $host = substr($address, 1, $closeBracket - 1);
        if ($host === '') {
            $host = '::';
        }

        // Check for :port after the closing bracket
        $port = 0;
        if (($closeBracket + 1) < strlen($address) && $address[$closeBracket + 1] === ':') {
            $port = (int) substr($address, $closeBracket + 2);
        }

        if ($port < 0 || $port > 65_535) {
            throw new Network\Exception\RuntimeException("Invalid port number in address: {$port}");
        }

        return Network\Address::udp($host, $port);
    }

    // IPv4: host:port
    $lastColon = strrpos($address, ':');
    if ($lastColon === false) {
        return Network\Address::udp($address, 0);
    }

    $host = substr($address, 0, $lastColon);
    $port = (int) substr($address, $lastColon + 1);

    $host = $host !== '' ? $host : '0.0.0.0';
    if ($port < 0 || $port > 65_535) {
        throw new Network\Exception\RuntimeException("Invalid port number in address: {$port}");
    }

    return Network\Address::udp($host, $port);
}
