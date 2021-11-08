<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl;
use Psl\Network;

use function fclose;

final class Server implements Network\ServerInterface
{
    /**
     * @param resource|null $impl
     */
    private function __construct(
        private mixed $impl
    ) {
    }

    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $file
     *
     * @throws Psl\Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(string $file): self
    {
        $socket = Network\Internal\server_listen("unix://{$file}");

        return new self($socket);
    }

    /**
     * {@inheritDoc}
     */
    public function nextConnection(): SocketInterface
    {
        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        // @codeCoverageIgnoreStart
        try {
            /** @psalm-suppress MissingThrowsDocblock */
            return new Internal\Socket(
                Network\Internal\socket_accept($this->impl)
            );
        } catch (Network\Exception\AlreadyStoppedException $exception) {
            $this->impl = null;

            throw $exception;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalAddress(): Network\Address
    {
        if (null === $this->impl) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        return Network\Internal\get_sock_name($this->impl);
    }

    /**
     * {@inheritDoc}
     */
    public function stopListening(): void
    {
        if (null === $this->impl) {
            return;
        }

        $resource = $this->impl;
        $this->impl = null;
        fclose($resource);
    }

    public function __destruct()
    {
        $this->stopListening();
    }
}
