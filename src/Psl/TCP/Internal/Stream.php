<?php

declare(strict_types=1);

namespace Psl\TCP\Internal;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\IO\Exception;
use Psl\IO\Internal\ResourceHandle;
use Psl\Network;
use Psl\Network\Address;
use Psl\TCP;
use Revolt\EventLoop;
use Socket as PHPSocket;
use Throwable;

use function is_resource;
use function socket_get_option;
use function socket_import_stream;
use function socket_set_option;
use function str_contains;
use function stream_socket_recvfrom;
use function stream_socket_shutdown;

use const IPPROTO_IP;
use const IPPROTO_IPV6;
use const PHP_OS_FAMILY;
use const SO_KEEPALIVE;
use const SO_LINGER;
use const SO_RCVBUF;
use const SO_SNDBUF;
use const SOL_SOCKET;
use const SOL_TCP;
use const STREAM_PEEK;
use const STREAM_SHUT_WR;
use const TCP_NODELAY;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Stream implements TCP\StreamInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;
    use IO\ReadHandleConvenienceMethodsTrait;

    private ResourceHandle $handle;

    /**
     * @param resource|PHPSocket $stream
     */
    public function __construct(mixed $stream)
    {
        $this->handle = new ResourceHandle($stream, read: true, write: true, seek: false, close: true);
    }

    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->handle->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        return $this->handle->tryRead($max_bytes);
    }

    /**
     * @param ?positive-int $max_bytes
     */
    #[Override]
    public function read(null|int $max_bytes = null, null|Duration $timeout = null): string
    {
        return $this->handle->read($max_bytes, $timeout);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->handle->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     */
    #[Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->handle->write($bytes, $timeout);
    }

    /**
     * @return resource|object|null
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->handle->getStream();
    }

    #[Override]
    public function getLocalAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        return Network\Internal\get_sock_name($stream);
    }

    #[Override]
    public function getPeerAddress(): Address
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        return Network\Internal\get_peer_name($stream);
    }

    /**
     * @param positive-int $max_bytes
     */
    #[Override]
    public function peek(int $max_bytes, null|Duration $timeout = null): string
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        $suspension = EventLoop::getSuspension();
        $timeout_watcher = null;

        if ($timeout !== null) {
            $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use (
                $suspension,
            ): void {
                $suspension->resume(true);
            });
        }

        $read_watcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
            EventLoop::cancel($watcher);
            $suspension->resume(false);
        });

        /** @var bool $timed_out */
        $timed_out = $suspension->suspend();
        if ($timeout_watcher !== null) {
            EventLoop::cancel($timeout_watcher);
        }

        EventLoop::cancel($read_watcher);

        if ($timed_out) {
            throw new IO\Exception\TimeoutException('Peek operation timed out.');
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $data = @stream_socket_recvfrom($stream, $max_bytes, STREAM_PEEK);
        if ($data === false) {
            throw new IO\Exception\RuntimeException('Failed to peek data from stream.');
        }

        return $data;
    }

    #[Override]
    public function shutdown(): void
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        $result = @stream_socket_shutdown($stream, STREAM_SHUT_WR);
        if ($result === false) {
            throw new IO\Exception\RuntimeException('Failed to shut down the write side of the stream.');
        }
    }

    #[Override]
    public function setNoDelay(bool $enabled): void
    {
        $socket = $this->getSocketResource();
        if (!@socket_set_option($socket, SOL_TCP, TCP_NODELAY, $enabled ? 1 : 0)) {
            throw new Network\Exception\RuntimeException('Failed to set TCP_NODELAY option.');
        }
    }

    #[Override]
    public function getNoDelay(): bool
    {
        $socket = $this->getSocketResource();
        $value = @socket_get_option($socket, SOL_TCP, TCP_NODELAY);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get TCP_NODELAY option.');
        }

        return (bool) $value;
    }

    #[Override]
    public function setTtl(int $ttl): void
    {
        $socket = $this->getSocketResource();
        [$level, $option] = $this->getTtlSocketOption();
        if (!@socket_set_option($socket, $level, $option, $ttl)) {
            throw new Network\Exception\RuntimeException('Failed to set TTL option.');
        }
    }

    #[Override]
    public function getTtl(): int
    {
        $socket = $this->getSocketResource();
        [$level, $option] = $this->getTtlSocketOption();
        $value = @socket_get_option($socket, $level, $option);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get TTL option.');
        }

        return (int) $value;
    }

    #[Override]
    public function setKeepAlive(bool $enabled): void
    {
        $socket = $this->getSocketResource();
        if (!@socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, $enabled ? 1 : 0)) {
            throw new Network\Exception\RuntimeException('Failed to set SO_KEEPALIVE option.');
        }
    }

    #[Override]
    public function getKeepAlive(): bool
    {
        $socket = $this->getSocketResource();
        $value = @socket_get_option($socket, SOL_SOCKET, SO_KEEPALIVE);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get SO_KEEPALIVE option.');
        }

        return (bool) $value;
    }

    /**
     * @param int<1, max> $size
     */
    #[Override]
    public function setSendBufferSize(int $size): void
    {
        $socket = $this->getSocketResource();
        if (!@socket_set_option($socket, SOL_SOCKET, SO_SNDBUF, $size)) {
            throw new Network\Exception\RuntimeException('Failed to set SO_SNDBUF option.');
        }
    }

    /**
     * @return int<1, max>
     */
    #[Override]
    public function getSendBufferSize(): int
    {
        $socket = $this->getSocketResource();
        $value = @socket_get_option($socket, SOL_SOCKET, SO_SNDBUF);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get SO_SNDBUF option.');
        }

        /** @var int<1, max> */
        return (int) $value;
    }

    /**
     * @param int<1, max> $size
     */
    #[Override]
    public function setReceiveBufferSize(int $size): void
    {
        $socket = $this->getSocketResource();
        if (!@socket_set_option($socket, SOL_SOCKET, SO_RCVBUF, $size)) {
            throw new Network\Exception\RuntimeException('Failed to set SO_RCVBUF option.');
        }
    }

    /**
     * @return int<1, max>
     */
    #[Override]
    public function getReceiveBufferSize(): int
    {
        $socket = $this->getSocketResource();
        $value = @socket_get_option($socket, SOL_SOCKET, SO_RCVBUF);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get SO_RCVBUF option.');
        }

        /** @var int<1, max> */
        return (int) $value;
    }

    #[Override]
    public function setLinger(null|Duration $duration): void
    {
        $socket = $this->getSocketResource();
        $linger = $duration === null
            ? ['l_onoff' => 0, 'l_linger' => 0]
            : ['l_onoff' => 1, 'l_linger' => (int) $duration->getTotalSeconds()];

        if (!@socket_set_option($socket, SOL_SOCKET, SO_LINGER, $linger)) {
            throw new Network\Exception\RuntimeException('Failed to set SO_LINGER option.');
        }
    }

    #[Override]
    public function getLinger(): null|Duration
    {
        $socket = $this->getSocketResource();
        /** @var array{l_onoff: int, l_linger: int}|false $value */
        $value = @socket_get_option($socket, SOL_SOCKET, SO_LINGER);
        if ($value === false) {
            throw new Network\Exception\RuntimeException('Failed to get SO_LINGER option.');
        }

        if ($value['l_onoff'] === 0) {
            return null;
        }

        return Duration::seconds($value['l_linger']);
    }

    #[Override]
    public function close(): void
    {
        $this->handle->close();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Get the appropriate socket level and option for TTL based on IP version.
     *
     * @return array{int, int}
     */
    private function getTtlSocketOption(): array
    {
        if ($this->isIpv6()) {
            // IPV6_UNICAST_HOPS: Linux = 16, macOS/BSD = 4
            return [IPPROTO_IPV6, PHP_OS_FAMILY === 'Darwin' ? 4 : 16];
        }

        // IP_TTL: Linux = 2, macOS/BSD = 4 (not defined as a PHP constant)
        return [IPPROTO_IP, PHP_OS_FAMILY === 'Darwin' ? 4 : 2];
    }

    private function isIpv6(): bool
    {
        try {
            return str_contains($this->getLocalAddress()->host, ':');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return PHPSocket
     */
    private function getSocketResource(): PHPSocket
    {
        $stream = $this->handle->getStream();
        if (!is_resource($stream)) {
            throw new Exception\AlreadyClosedException('Stream handle has already been closed.');
        }

        $socket = @socket_import_stream($stream);
        if ($socket === false) {
            throw new Network\Exception\RuntimeException('Failed to import stream as socket.');
        }

        return $socket;
    }
}
