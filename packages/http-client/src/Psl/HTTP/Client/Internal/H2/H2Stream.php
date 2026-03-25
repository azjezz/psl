<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H2;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HPACK\Header;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Response;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;
use Throwable;

use function strlen;
use function substr;

/**
 * Per-stream state container for an HTTP/2 exchange.
 *
 * The {@see H2Multiplexer} dispatches H2 events into this object. Consumers
 * ({@see StreamExchange}, {@see ResponseBodyHandle}) read from it. This
 * decouples the read fiber's lifecycle from body consumption.
 *
 * @internal
 */
final class H2Stream
{
    /**
     * @var Async\Deferred<array{int<100, 999>, FieldMap, bool}>
     */
    private Async\Deferred $responseDeferred;

    /**
     * @var list<Response>
     */
    private array $informationalResponses = [];

    private bool $responseResolved = false;

    private string $bodyBuffer = '';

    private bool $bodyComplete = false;

    private null|Suspension $bodyWaiter = null;

    private null|FieldMap $trailers = null;

    private null|Throwable $error = null;

    public function __construct(
        public readonly int $streamId,
    ) {
        /** @var Async\Deferred<array{int<100, 999>, FieldMap, bool}> */
        $this->responseDeferred = new Async\Deferred();
    }

    /**
     * Called by the multiplexer when HEADERS arrive.
     *
     * If the response deferred hasn't been resolved yet, this is a response
     * (possibly informational). If already resolved, this is trailing headers.
     *
     * @param list<Header> $headers Raw HPACK-decoded headers.
     * @param bool $endStream Whether END_STREAM flag was set.
     */
    public function handleHeaders(array $headers, bool $endStream): void
    {
        if ($this->responseResolved) {
            $this->handleTrailers($headers);
            return;
        }

        /** @var int<100, 999>|null $status */
        $status = null;
        /** @var list<list{non-empty-string, string}> $fields */
        $fields = [];

        foreach ($headers as $header) {
            if ($header->name === ':status') {
                /** @var int<100, 999> $status */
                $status = (int) $header->value;
                continue;
            }

            if ($header->name[0] === ':') {
                continue;
            }

            $fields[] = [$header->name, $header->value];
        }

        if ($status === null) {
            $this->fail(ProtocolException::forMalformedResponse('Missing :status pseudo-header.'));
            return;
        }

        if ($status < 200) {
            $this->informationalResponses[] = new Response(
                status: $status,
                protocolVersion: ProtocolVersion::V20,
                headers: FieldMap::from($fields),
            );
            return;
        }

        $this->responseResolved = true;

        if ($endStream) {
            $this->bodyComplete = true;
            $this->trailers = FieldMap::default();
        }

        $this->responseDeferred->complete([$status, FieldMap::from($fields), $endStream]);
    }

    /**
     * Called by the multiplexer when DATA arrives.
     */
    public function handleData(string $data, bool $endStream): void
    {
        $this->bodyBuffer .= $data;

        if ($endStream) {
            $this->bodyComplete = true;
            $this->trailers ??= FieldMap::default();
        }

        $this->wakeBodyWaiter();
    }

    /**
     * Called by the multiplexer on error (GOAWAY, RST_STREAM, connection close).
     */
    public function fail(Throwable $error): void
    {
        if ($this->error !== null) {
            return;
        }

        $this->error = $error;
        $this->bodyComplete = true;

        if (!$this->responseResolved) {
            $this->responseResolved = true;
            $this->responseDeferred->error($error);
        }

        $this->wakeBodyWaiter();
    }

    /**
     * Wait for the final response HEADERS.
     *
     * @return array{int<100, 999>, FieldMap, bool} [status, headers, endStream]
     *
     * @throws Throwable If the stream fails before headers arrive.
     * @throws Async\Exception\CancelledException If cancelled.
     */
    public function awaitResponse(CancellationTokenInterface $cancellation): array
    {
        return $this->responseDeferred->getAwaitable()->await($cancellation);
    }

    /**
     * @return list<Response>
     */
    public function getInformationalResponses(): array
    {
        return $this->informationalResponses;
    }

    /**
     * Read body data, blocking if the buffer is empty and the stream is not complete.
     *
     * @throws Throwable If the stream encountered an error.
     * @throws Async\Exception\CancelledException If cancelled.
     */
    public function readBody(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $this->throwIfErrored();

        if ($this->bodyBuffer !== '') {
            return $this->consumeBuffer($maxBytes);
        }

        if ($this->bodyComplete) {
            return '';
        }

        $suspension = EventLoop::getSuspension();
        $this->bodyWaiter = $suspension;

        $id = null;
        if ($cancellation->cancellable) {
            $id = $cancellation->subscribe(static function (Async\Exception\CancelledException $e) use (
                $suspension,
            ): void {
                $suspension->throw($e);
            });
        }

        try {
            $suspension->suspend();
        } finally {
            if ($id !== null) {
                $cancellation->unsubscribe($id);
            }

            $this->bodyWaiter = null;
        }

        $this->throwIfErrored();

        return $this->consumeBuffer($maxBytes);
    }

    /**
     * Non-blocking read - return buffered data only.
     */
    public function tryReadBody(null|int $maxBytes = null): string
    {
        $this->throwIfErrored();

        if ($this->bodyBuffer === '') {
            return '';
        }

        return $this->consumeBuffer($maxBytes);
    }

    public function isBodyComplete(): bool
    {
        return $this->bodyComplete && $this->bodyBuffer === '';
    }

    public function getTrailers(): null|FieldMap
    {
        return $this->trailers;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): null|Throwable
    {
        return $this->error;
    }

    /**
     * Parse trailing HEADERS into FieldMap.
     *
     * @param list<Header> $headers
     */
    private function handleTrailers(array $headers): void
    {
        /** @var list<list{non-empty-string, string}> $fields */
        $fields = [];
        foreach ($headers as $header) {
            if ($header->name[0] === ':') {
                continue;
            }

            $fields[] = [$header->name, $header->value];
        }

        $this->trailers = FieldMap::from($fields);
        $this->bodyComplete = true;
        $this->wakeBodyWaiter();
    }

    private function wakeBodyWaiter(): void
    {
        if ($this->bodyWaiter !== null) {
            $suspension = $this->bodyWaiter;
            $this->bodyWaiter = null;
            $suspension->resume();
        }
    }

    /**
     * @throws Throwable
     */
    private function throwIfErrored(): void
    {
        if ($this->error !== null) {
            throw $this->error;
        }
    }

    private function consumeBuffer(null|int $maxBytes): string
    {
        if ($this->bodyBuffer === '') {
            return '';
        }

        if ($maxBytes === null || $maxBytes >= strlen($this->bodyBuffer)) {
            $data = $this->bodyBuffer;
            $this->bodyBuffer = '';
            return $data;
        }

        $data = substr($this->bodyBuffer, 0, $maxBytes);
        $this->bodyBuffer = substr($this->bodyBuffer, $maxBytes);
        return $data;
    }
}
