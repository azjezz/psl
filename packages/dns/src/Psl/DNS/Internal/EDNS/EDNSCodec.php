<?php

declare(strict_types=1);

namespace Psl\DNS\Internal\EDNS;

use OutOfBoundsException;
use Psl\DNS\EDNS\CookieOption;
use Psl\DNS\EDNS\ECSOption;
use Psl\DNS\EDNS\ExtendedDNSErrorOption;
use Psl\DNS\EDNS\KeyTagOption;
use Psl\DNS\EDNS\NSIDOption;
use Psl\DNS\EDNS\OptionInterface;
use Psl\DNS\EDNS\PaddingOption;
use Psl\DNS\EDNS\RawOption;
use Psl\DNS\EDNS\TCPKeepaliveOption;
use Psl\DNS\Exception\InvalidArgumentException;
use Psl\DNS\Exception\ProtocolException;
use Psl\IP\Address;
use Psl\IP\Family;

use function ord;
use function pack;
use function str_pad;
use function strlen;
use function substr;
use function unpack;

/**
 * Encodes and decodes EDNS0 option TLV pairs (RFC 6891 Section 6.1.2).
 *
 * @internal
 */
final class EDNSCodec
{
    private const int NSID_OPTION_CODE = 3;
    private const int ECS_OPTION_CODE = 8;
    private const int COOKIE_OPTION_CODE = 10;
    private const int TCP_KEEPALIVE_OPTION_CODE = 11;
    private const int PADDING_OPTION_CODE = 12;
    private const int KEY_TAG_OPTION_CODE = 14;
    private const int EXTENDED_DNS_ERROR_OPTION_CODE = 15;
    private const int CLIENT_COOKIE_LENGTH = 8;

    /**
     * Decode EDNS0 options from raw OPT RDATA.
     *
     * @return list<OptionInterface>
     *@throws ProtocolException If the OPT record data is malformed.
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    public static function decodeOptions(string $data): array
    {
        if ($data === '') {
            return [];
        }

        $options = [];
        $length = strlen($data);
        $offset = 0;

        while ($offset < $length) {
            if (($offset + 4) > $length) {
                throw new OutOfBoundsException('Read beyond end of EDNS option data at offset ' . $offset);
            }

            $code = unpack('n', $data, $offset)[1];
            $offset += 2;
            $optionLength = unpack('n', $data, $offset)[1];
            $offset += 2;
            /** @var non-negative-int $optionLength */

            if ($code === self::NSID_OPTION_CODE) {
                $options[] = self::decodeNsid($data, $offset, $optionLength);
            } elseif ($code === self::ECS_OPTION_CODE) {
                $options[] = self::decodeEcs($data, $offset, $length, $optionLength);
            } elseif ($code === self::COOKIE_OPTION_CODE) {
                $options[] = self::decodeCookie($data, $offset, $length, $optionLength);
            } elseif ($code === self::TCP_KEEPALIVE_OPTION_CODE) {
                $options[] = self::decodeTcpKeepalive($data, $offset, $length, $optionLength);
            } elseif ($code === self::PADDING_OPTION_CODE) {
                $options[] = self::decodePadding($offset, $optionLength);
            } elseif ($code === self::KEY_TAG_OPTION_CODE) {
                $options[] = self::decodeKeyTag($data, $offset, $length, $optionLength);
            } elseif ($code === self::EXTENDED_DNS_ERROR_OPTION_CODE) {
                $options[] = self::decodeExtendedDnsError($data, $offset, $length, $optionLength);
            } else {
                if ($optionLength > 0) {
                    if (($offset + $optionLength) > $length) {
                        throw new OutOfBoundsException('Read beyond end of EDNS option data at offset ' . $offset);
                    }

                    $optionData = substr($data, $offset, $optionLength);
                    $offset += $optionLength;
                } else {
                    $optionData = '';
                }

                $options[] = new RawOption($code, $optionData);
            }
        }

        return $options;
    }

    /**
     * Encode EDNS0 options into raw bytes for OPT RDATA.
     *
     * @param list<OptionInterface> $options
     *
     * @throws InvalidArgumentException If an EDNS option cannot be encoded.
     */
    public static function encodeOptions(array $options): string
    {
        if ($options === []) {
            return '';
        }

        $result = '';
        foreach ($options as $option) {
            $wireData = $option->toWireFormat();
            $result .= pack('nn', $option->code, strlen($wireData)) . $wireData;
        }

        return $result;
    }

    /**
     * Decode an ECS option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     * @throws ProtocolException If the OPT record data is malformed.
     */
    private static function decodeEcs(string $data, int &$offset, int $length, int $optionLength): ECSOption
    {
        if (($offset + 4) > $length) {
            throw new OutOfBoundsException('Read beyond end of ECS option data at offset ' . $offset);
        }

        $ianaFamily = unpack('n', $data, $offset)[1];
        $offset += 2;
        $byteSize = match ($ianaFamily) {
            1 => 4,
            2 => 16,
            default => throw ProtocolException::forUnknownECSAddressFamily(),
        };

        $family = Family::from($byteSize);

        $sourcePrefixLength = ord($data[$offset++]);
        $scopePrefixLength = ord($data[$offset++]);

        $addressLength = $optionLength - 4;
        /** @var non-negative-int $addressLength */
        $addressBytes = $addressLength > 0 ? substr($data, $offset, $addressLength) : '';
        $offset += $addressLength;

        /** @var non-empty-string $padded */
        $padded = str_pad($addressBytes, $family->value, "\x00");

        $address = Address::fromBytes($padded);

        return new ECSOption($address, $sourcePrefixLength, $scopePrefixLength);
    }

    /**
     * Decode a DNS COOKIE option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    private static function decodeCookie(string $data, int &$offset, int $length, int $optionLength): CookieOption
    {
        if (($offset + self::CLIENT_COOKIE_LENGTH) > $length) {
            throw new OutOfBoundsException('Read beyond end of cookie option data at offset ' . $offset);
        }

        /** @var non-empty-string $clientCookie */
        $clientCookie = substr($data, $offset, self::CLIENT_COOKIE_LENGTH);
        $offset += self::CLIENT_COOKIE_LENGTH;

        $serverCookieLength = $optionLength - self::CLIENT_COOKIE_LENGTH;
        /** @var non-negative-int $serverCookieLength */
        $serverCookie = $serverCookieLength > 0 ? substr($data, $offset, $serverCookieLength) : '';
        $offset += $serverCookieLength;

        return new CookieOption($clientCookie, $serverCookie);
    }

    /**
     * Decode an NSID option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    private static function decodeNsid(string $data, int &$offset, int $optionLength): NSIDOption
    {
        $id = $optionLength > 0 ? substr($data, $offset, $optionLength) : '';
        $offset += $optionLength;

        return new NSIDOption($id);
    }

    /**
     * Decode a TCP Keepalive option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    private static function decodeTcpKeepalive(
        string $data,
        int &$offset,
        int $length,
        int $optionLength,
    ): TCPKeepaliveOption {
        $timeout = null;
        if ($optionLength >= 2) {
            if (($offset + 2) > $length) {
                throw new OutOfBoundsException('Read beyond end of keepalive option data at offset ' . $offset);
            }

            $timeout = unpack('n', $data, $offset)[1];
            $offset += 2;
        }

        return new TCPKeepaliveOption($timeout);
    }

    /**
     * Decode a Padding option from the data.
     *
     * @param non-negative-int $optionLength
     */
    private static function decodePadding(int &$offset, int $optionLength): PaddingOption
    {
        $offset += $optionLength;

        return new PaddingOption($optionLength);
    }

    /**
     * Decode a Key Tag option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    private static function decodeKeyTag(string $data, int &$offset, int $length, int $optionLength): KeyTagOption
    {
        $tags = [];
        /** @var non-negative-int $tagCount */
        $tagCount = (int) ($optionLength / 2);
        for ($i = 0; $i < $tagCount; $i++) {
            if (($offset + 2) > $length) {
                throw new OutOfBoundsException('Read beyond end of key tag option data at offset ' . $offset);
            }

            $tags[] = unpack('n', $data, $offset)[1];
            $offset += 2;
        }

        return new KeyTagOption($tags);
    }

    /**
     * Decode an Extended DNS Error option from the data.
     *
     * @param non-negative-int $optionLength
     *
     * @throws OutOfBoundsException If the option data is truncated.
     */
    private static function decodeExtendedDnsError(
        string $data,
        int &$offset,
        int $length,
        int $optionLength,
    ): ExtendedDNSErrorOption {
        if (($offset + 2) > $length) {
            throw new OutOfBoundsException('Read beyond end of extended DNS error option data at offset ' . $offset);
        }

        $infoCode = unpack('n', $data, $offset)[1];
        $offset += 2;
        $textLength = $optionLength - 2;
        /** @var non-negative-int $textLength */
        $extraText = $textLength > 0 ? substr($data, $offset, $textLength) : '';
        $offset += $textLength;

        return new ExtendedDNSErrorOption($infoCode, $extraText);
    }
}
