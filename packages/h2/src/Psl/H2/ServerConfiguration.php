<?php

declare(strict_types=1);

namespace Psl\H2;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Configuration for a server-side HTTP/2 connection.
 *
 * @see ServerConnection
 */
final readonly class ServerConfiguration implements DefaultInterface
{
    /**
     * @param array<positive-int, non-negative-int> $settings Local settings overrides (setting ID => value).
     * @param null|RateLimiter $rateLimiter Optional rate limiter for incoming frames.
     * @param int $maxHeaderBlockSize Maximum accumulated header block size in bytes (0 = unlimited).
     * @param null|int<1, max> $maxReceiveWindowSize Maximum receive window size for BDP auto-tuning.
     *                                               Null disables BDP auto-tuning; a value enables it
     *                                               and caps the dynamically adjusted window at this size.
     * @param positive-int $writeBufferThreshold Buffered write data is flushed when it reaches this size in bytes.
     */
    public function __construct(
        public array $settings = [],
        public null|RateLimiter $rateLimiter = null,
        public int $maxHeaderBlockSize = 0,
        public null|int $maxReceiveWindowSize = null,
        public int $writeBufferThreshold = 65_536,
    ) {}

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Return a new configuration with the given settings overrides.
     *
     * @param array<positive-int, non-negative-int> $settings
     */
    public function withSettings(array $settings): self
    {
        return new self(
            $settings,
            $this->rateLimiter,
            $this->maxHeaderBlockSize,
            $this->maxReceiveWindowSize,
            $this->writeBufferThreshold,
        );
    }

    /**
     * Return a new configuration with the given rate limiter.
     */
    public function withRateLimiter(null|RateLimiter $rateLimiter): self
    {
        return new self(
            $this->settings,
            $rateLimiter,
            $this->maxHeaderBlockSize,
            $this->maxReceiveWindowSize,
            $this->writeBufferThreshold,
        );
    }

    /**
     * Return a new configuration with the given maximum header block size.
     *
     * @param int<0, max> $maxHeaderBlockSize 0 for unlimited.
     */
    public function withMaxHeaderBlockSize(int $maxHeaderBlockSize): self
    {
        return new self(
            $this->settings,
            $this->rateLimiter,
            $maxHeaderBlockSize,
            $this->maxReceiveWindowSize,
            $this->writeBufferThreshold,
        );
    }

    /**
     * Return a new configuration with BDP auto-tuning enabled at the given window cap.
     *
     * @param null|int<1, max> $maxReceiveWindowSize Null to disable, or the maximum window size in bytes.
     */
    public function withMaxReceiveWindowSize(null|int $maxReceiveWindowSize): self
    {
        return new self(
            $this->settings,
            $this->rateLimiter,
            $this->maxHeaderBlockSize,
            $maxReceiveWindowSize,
            $this->writeBufferThreshold,
        );
    }

    /**
     * Return a new configuration with the given write buffer flush threshold.
     *
     * @param positive-int $writeBufferThreshold
     */
    public function withWriteBufferThreshold(int $writeBufferThreshold): self
    {
        return new self(
            $this->settings,
            $this->rateLimiter,
            $this->maxHeaderBlockSize,
            $this->maxReceiveWindowSize,
            $writeBufferThreshold,
        );
    }
}
