<?php

declare(strict_types=1);

namespace Psl\SMTP\Client;

use Override;
use Psl\Default\DefaultInterface;
use Psl\SMTP\Security;
use Psl\TCP;
use Psl\TLS;

/**
 * Configuration for the SMTP transport.
 *
 * @api
 *
 * @mago-expect lint:excessive-parameter-list
 */
final readonly class TransportConfiguration implements DefaultInterface
{
    /**
     * @param non-empty-string $host
     * @param int<0, 65535>|null $port Defaults to 587 for StartTLS, 465 for TLS, 25 for None.
     * @param non-empty-string $localHostname The hostname to use in EHLO/HELO commands.
     * @param bool $pipelining Whether to use SMTP pipelining (RFC 2920) when supported by the server.
     * @param bool $chunking Whether to use BDAT chunking (RFC 3030) when supported by the server.
     * @param int<1, max> $chunkSize The size in bytes of each BDAT chunk.
     * @param bool $allowPartialSuccess Whether to deliver to accepted recipients when some are rejected.
     */
    public function __construct(
        public string $host = 'localhost',
        public null|int $port = null,
        public Security $security = Security::StartTLS,
        public string $localHostname = 'localhost',
        public bool $pipelining = true,
        public bool $chunking = true,
        public int $chunkSize = 65_536,
        public bool $allowPartialSuccess = false,
        public TCP\ConnectConfiguration $connectConfiguration = new TCP\ConnectConfiguration(),
        public TLS\ClientConfiguration $tlsClientConfiguration = new TLS\ClientConfiguration(),
    ) {}

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @param non-empty-string $host
     */
    public function withHost(string $host): self
    {
        return new self(
            $host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    /**
     * @param int<0, 65535>|null $port
     */
    public function withPort(null|int $port): self
    {
        return new self(
            $this->host,
            $port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withSecurity(Security $security): self
    {
        return new self(
            $this->host,
            $this->port,
            $security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    /**
     * @param non-empty-string $localHostname
     */
    public function withLocalHostname(string $localHostname): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withPipelining(bool $pipelining): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withChunking(bool $chunking): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    /**
     * @param int<1, max> $chunkSize
     */
    public function withChunkSize(int $chunkSize): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withAllowPartialSuccess(bool $allowPartialSuccess): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $allowPartialSuccess,
            $this->connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withConnectConfiguration(TCP\ConnectConfiguration $connectConfiguration): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $connectConfiguration,
            $this->tlsClientConfiguration,
        );
    }

    public function withTlsClientConfiguration(TLS\ClientConfiguration $tlsClientConfiguration): self
    {
        return new self(
            $this->host,
            $this->port,
            $this->security,
            $this->localHostname,
            $this->pipelining,
            $this->chunking,
            $this->chunkSize,
            $this->allowPartialSuccess,
            $this->connectConfiguration,
            $tlsClientConfiguration,
        );
    }
}
