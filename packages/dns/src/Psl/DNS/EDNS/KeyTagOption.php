<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

use function pack;

/**
 * EDNS Key Tag option (RFC 8145, option code 14).
 *
 * Signals which DNSSEC trust anchor key tags a validating resolver
 * is currently using, allowing operators to monitor key rollover.
 *
 * @api
 */
final class KeyTagOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 14;
    }

    /**
     * @param list<int<0, 65535>> $tags DNSSEC key tags.
     */
    public function __construct(
        public readonly array $tags,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        $result = '';
        foreach ($this->tags as $tag) {
            $result .= pack('n', $tag);
        }

        return $result;
    }
}
