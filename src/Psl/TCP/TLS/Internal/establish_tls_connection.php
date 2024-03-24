<?php

declare(strict_types=1);

namespace Psl\TCP\TLS\Internal;

use Psl\Async\OptionalIncrementalTimeout;
use Psl\DateTime\Duration;
use Psl\Internal;
use Psl\IO\Internal as IO;
use Psl\Network\Exception;
use Psl\TCP\TLS\Exception\NegotiationException;
use Revolt\EventLoop;

use function feof;
use function is_resource;
use function restore_error_handler;
use function set_error_handler;
use function stream_context_set_option;
use function stream_context_set_options;
use function stream_socket_enable_crypto;

/**
 * Establish a TLS connection on a given stream resource.
 *
 * This function configures the TLS context for the stream and attempts to enable crypto.
 *
 * If enabling crypto fails or a timeout occurs, it throws an appropriate exception.
 *
 * @internal
 *
 * @param resource $resource The stream resource to be secured with TLS.
 * @param array $context The TLS context options.
 *
 * @throws NegotiationException If TLS negotiation fails
 * @throws Exception\TimeoutException If the TLS negotiation times out.
 */
function establish_tls_connection(mixed $resource, array $context, ?Duration $timeout = null): void
{
    Internal\suppress(static function () use ($resource, $context, $timeout): void {
        $optional_timeout = new OptionalIncrementalTimeout($timeout, static function (): void {
            throw new Exception\TimeoutException('TLS negotiation timed out.');
        });

        if (PHP_VERSION_ID >= 80300) {
            /**
             * @psalm-suppress UnusedFunctionCall
             */
            stream_context_set_options($resource, ['ssl' => $context]);
        } else {
            stream_context_set_option($resource, ['ssl' => $context]);
        }

        $error_handler = static function (int $code, string $message) use ($resource): never {
            if (feof($resource)) {
                $message = 'Connection reset by peer';
            }

            throw new NegotiationException('TLS negotiation failed: ' . $message);
        };

        try {
            set_error_handler($error_handler);
            $result = stream_socket_enable_crypto($resource, enable: true);

            if ($result === false) {
                throw new NegotiationException('TLS negotiation failed: Unknown error');
            }
        } finally {
            restore_error_handler();
        }

        if (true !== $result) {
            while (true) {
                $suspension = EventLoop::getSuspension();

                $read_watcher = '';
                $timeout_watcher = '';
                $timeout = $optional_timeout->getRemaining();
                if (null !== $timeout) {
                    $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use ($suspension, &$read_watcher, $resource) {
                        EventLoop::cancel($read_watcher);

                        /** @psalm-suppress RedundantCondition - it can be resource|closed-resource */
                        if (is_resource($resource)) {
                            IO\close_resource($resource);
                        }

                        $suspension->throw(new Exception\TimeoutException('TLS negotiation timed out.'));
                    });
                }

                $read_watcher = EventLoop::onReadable($resource, static function () use ($suspension, $timeout_watcher) {
                    EventLoop::cancel($timeout_watcher);

                    $suspension->resume();
                });

                try {
                    $suspension->suspend();
                } finally {
                    EventLoop::cancel($read_watcher);
                    EventLoop::cancel($timeout_watcher);
                }

                try {
                    set_error_handler($error_handler);
                    $result = stream_socket_enable_crypto($resource, enable: true);
                    if ($result === false) {
                        $message = feof($resource) ? 'Connection reset by peer' : 'Unknown error';
                        throw new NegotiationException('TLS negotiation failed: ' . $message);
                    }
                } finally {
                    restore_error_handler();
                }

                if ($result === true) {
                    break;
                }
            }
        }
    });
}
