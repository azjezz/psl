<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\DateTime\Duration;
use Psl\Network;
use Revolt\EventLoop;

/**
 * A connector that retries failed connections with exponential backoff.
 *
 * Wraps any {@see ConnectorInterface} and automatically retries on failure.
 * The delay between attempts grows exponentially: backoff * multiplier^attempt.
 */
final readonly class RetryConnector implements ConnectorInterface
{
    private Duration $backoff;

    /**
     * @param ConnectorInterface $connector The underlying connector to use.
     * @param int<1, 10> $maxAttempts Maximum number of connection attempts.
     * @param Duration|null $backoff Base delay before first retry. Defaults to 100ms.
     * @param int<1, max> $backoffMultiplier Exponential multiplier for each subsequent retry.
     */
    public function __construct(
        private ConnectorInterface $connector,
        private int $maxAttempts = 3,
        null|Duration $backoff = null,
        private int $backoffMultiplier = 2,
    ) {
        $this->backoff = $backoff ?? Duration::milliseconds(100);
    }

    #[Override]
    public function connect(string $host, int $port, null|Duration $timeout = null): StreamInterface
    {
        $attempts = 0;
        $messages = [];

        while (true) {
            $attempts++;

            try {
                return $this->connector->connect($host, $port, $timeout);
            } catch (Network\Exception\RuntimeException $e) {
                $messages[] = "Attempt {$attempts}: {$e->getMessage()}";

                if ($attempts >= $this->maxAttempts) {
                    throw new Network\Exception\RuntimeException(
                        "Failed to connect to {$host}:{$port} after {$attempts} attempts. " . implode(' ', $messages),
                        (int) $e->getCode(),
                        $e,
                    );
                }

                // Exponential backoff: base * multiplier^(attempt-1)
                $delaySec = $this->backoff->getTotalSeconds() * ($this->backoffMultiplier ** ($attempts - 1));

                $suspension = EventLoop::getSuspension();
                $watcher = EventLoop::delay($delaySec, static function () use ($suspension): void {
                    $suspension->resume();
                });

                try {
                    $suspension->suspend();
                } finally {
                    EventLoop::cancel($watcher);
                }
            }
        }
    }
}
