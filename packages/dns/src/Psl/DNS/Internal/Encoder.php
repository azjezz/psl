<?php

declare(strict_types=1);

namespace Psl\DNS\Internal;

use Psl\DNS\EDNS\OptionInterface;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Exception\RuntimeException;
use Psl\DNS\Internal\EDNS\EDNSCodec;
use Psl\DNS\Record\RecordType;
use Psl\Punycode;
use Psl\Punycode\Exception\ExceptionInterface;
use Random\RandomException;

use function chr;
use function explode;
use function mb_check_encoding;
use function pack;
use function random_int;
use function str_contains;
use function strlen;

/**
 * Builds DNS query packets per RFC 1035.
 *
 * @internal
 */
final class Encoder
{
    /**
     * Encode a standard recursive DNS query packet.
     *
     * @param list<OptionInterface> $ednsOptions
     *
     * @return array{int, string} Transaction ID and encoded packet bytes.
     *
     * @throws InvalidArgumentException If the domain name cannot be encoded to wire format or an EDNS option cannot be encoded.
     * @throws RuntimeException If secure random generation fails.
     */
    public static function encode(
        string $name,
        RecordType $kind,
        bool $dnssec = false,
        int $udpPayloadSize = 4096,
        array $ednsOptions = [],
    ): array {
        try {
            $id = random_int(0, 65_535);
        } catch (RandomException $e) {
            throw RuntimeException::forTransactionIDGenerationFailure($e);
        }

        $includeOpt = $dnssec || $ednsOptions !== [];
        $arcount = $includeOpt ? 1 : 0;

        $packet =
            pack('nnnnnn', $id, 0x0100, 1, 0, 0, $arcount) . self::encodeName($name) . pack('nn', $kind->value, 1);

        if ($includeOpt) {
            $ttl = $dnssec ? 0x0000_8000 : 0x0000_0000;
            $rdata = EDNSCodec::encodeOptions($ednsOptions);
            $rdlength = strlen($rdata);

            $packet .= pack('CnnNn', 0x00, 41, $udpPayloadSize, $ttl, $rdlength) . $rdata;
        }

        return [$id, $packet];
    }

    /**
     * Encode a domain name as a sequence of length-prefixed labels
     * terminated by a zero byte.
     *
     * Non-ASCII labels are converted to Punycode (ACE form) per RFC 5891.
     *
     * @throws InvalidArgumentException If the domain name cannot be encoded to wire format.
     */
    public static function encodeName(string $name): string
    {
        $labels = explode('.', $name);
        $encoded = '';
        foreach ($labels as $label) {
            if ($label !== '' && !mb_check_encoding($label, 'ASCII')) {
                try {
                    $label = 'xn--' . Punycode\encode($label);
                } catch (ExceptionInterface $e) {
                    throw InvalidArgumentException::forLabelTooLong($label);
                }
            }

            $length = strlen($label);
            if ($length > 63) {
                throw InvalidArgumentException::forLabelTooLong($label);
            }

            if ($length > 0) {
                if (str_contains($label, "\x00")) {
                    throw InvalidArgumentException::forLabelContainsNull();
                }

                $encoded .= chr($length) . $label;
            }
        }

        $encoded .= "\x00";

        $wireLength = strlen($encoded);
        if ($wireLength > 255) {
            throw InvalidArgumentException::forNameTooLong($name);
        }

        return $encoded;
    }
}
