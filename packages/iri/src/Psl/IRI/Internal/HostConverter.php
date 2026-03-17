<?php

declare(strict_types=1);

namespace Psl\IRI\Internal;

use Psl\IRI\Exception\InvalidIRIException;
use Psl\Punycode;
use Psl\URI\Authority\HostInterface;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;

use function preg_match;
use function str_contains;

/**
 * Converts host representations between Unicode (IRI) and ASCII (URI) forms.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
 *
 * @internal
 */
final class HostConverter
{
    /**
     * Convert a URI host to its Unicode (IRI) representation.
     *
     * Decodes Punycode-encoded labels (those starting with "xn--") to their
     * Unicode form while leaving IP addresses and plain ASCII hosts unchanged.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
     *
     * @throws Punycode\Exception\EncodingException If Punycode decoding fails.
     */
    public static function convertToUnicode(HostInterface $host): HostInterface
    {
        if ($host instanceof IPHost) {
            return $host;
        }

        if (str_contains($host->name, 'xn--')) {
            return new RegisteredNameHost(IDNA::toUnicode($host->name));
        }

        return $host;
    }

    /**
     * Convert an IRI host to its ASCII (URI) representation.
     *
     * Applies Punycode encoding to labels containing non-ASCII characters
     * while leaving IP addresses and plain ASCII hosts unchanged.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
     *
     * @throws Punycode\Exception\EncodingException If Punycode encoding fails.
     * @throws InvalidIRIException If the host contains invalid IDNA labels.
     */
    public static function convertToAscii(HostInterface $host): HostInterface
    {
        if ($host instanceof IPHost) {
            return $host;
        }

        if (preg_match('/[\x80-\xFF]/', $host->name)) {
            return new RegisteredNameHost(IDNA::toASCII($host->name));
        }

        return $host;
    }
}
