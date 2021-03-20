<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl\Asio\Awaitable;
use Throwable;

/**
 * @template T
 *
 * @psalm-suppress MissingConstructor
 */
final class Watcher
{
    public const IO = 0b00000011;
    public const READABLE = 0b00000001;
    public const WRITABLE = 0b00000010;
    public const DEFER = 0b00000100;
    public const TIMER = 0b00011000;
    public const DELAY = 0b00001000;
    public const REPEAT = 0b00010000;

    public int $type;

    public bool $enabled = true;

    public bool $referenced = true;

    public string $id;

    /**
     * @var callable
     */
    public $callback;

    /**
     * Data provided to the watcher callback.
     *
     * @var mixed
     */
    public $data;

    /**
     * Watcher-dependent value storage. Stream for IO watchers, signal number for signal watchers, interval for timers.
     *
     * @var T
     */
    public $value;

    /**
     * @var int|null Timer expiration timestamp.
     */
    public ?int $expiration = null;

    /**
     * @param resource $stream
     */
    public function executeStream($stream): void
    {
        /** @var mixed $result */
        $result = ($this->callback)($this->id, $stream, $this->data);

        if ($result instanceof Awaitable) {
            $result->onJoin(static function (?Throwable $ex): void {
                if ($ex) {
                    throw $ex;
                }
            });
        }
    }

    public function execute(): void
    {
        /** @var mixed $result */
        $result = ($this->callback)($this->id, $this->data);

        if ($result instanceof Awaitable) {
            $result->onJoin(static function (?Throwable $ex): void {
                if ($ex) {
                    throw $ex;
                }
            });
        }
    }
}
