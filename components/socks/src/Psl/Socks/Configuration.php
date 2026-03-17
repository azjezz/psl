<?php

declare(strict_types=1);

namespace Psl\Socks;

use SensitiveParameter;

/**
 * Configuration for SOCKS5 proxy connections.
 */
final readonly class Configuration
{
    /**
     * @param non-empty-string $proxyHost SOCKS5 proxy server hostname or IP.
     * @param int<0, 65535> $proxyPort SOCKS5 proxy server port.
     * @param non-empty-string|null $username Optional authentication username.
     * @param non-empty-string|null $password Optional authentication password.
     */
    public function __construct(
        public string $proxyHost,
        public int $proxyPort,
        public null|string $username = null,
        #[SensitiveParameter]
        public null|string $password = null,
    ) {}

    /**
     * @param non-empty-string $proxyHost
     *
     * @psalm-mutation-free
     */
    public function withProxyHost(string $proxyHost): self
    {
        return new self($proxyHost, $this->proxyPort, $this->username, $this->password);
    }

    /**
     * @param int<0, 65535> $proxyPort
     *
     * @psalm-mutation-free
     */
    public function withProxyPort(int $proxyPort): self
    {
        return new self($this->proxyHost, $proxyPort, $this->username, $this->password);
    }

    /**
     * @param non-empty-string|null $username
     * @param non-empty-string|null $password
     *
     * @psalm-mutation-free
     */
    public function withCredentials(null|string $username, #[SensitiveParameter] null|string $password): self
    {
        return new self($this->proxyHost, $this->proxyPort, $username, $password);
    }
}
