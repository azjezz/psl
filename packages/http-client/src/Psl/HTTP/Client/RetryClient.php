<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Message;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;

/**
 * Decorator that retries failed requests with exponential backoff.
 *
 * Wraps an inner {@see ClientInterface} and automatically retries requests that fail
 * with transport-level exceptions. Only idempotent HTTP methods (GET, HEAD, PUT,
 * DELETE, OPTIONS, TRACE) are retried, as defined by RFC 9110 Section 9.2.2.
 * Non-idempotent methods (POST, PATCH) propagate the exception immediately to avoid
 * unintended side effects from duplicate submissions.
 *
 * ## Retry triggers
 *
 * Retries are triggered only by transport-level exceptions:
 *
 * - {@see \Psl\Network\Exception\RuntimeException}: connection refused, DNS failure,
 *   connect timeout, connection reset.
 * - {@see \Psl\IO\Exception\RuntimeException}: read/write failure on the underlying
 *   socket (e.g., broken pipe, unexpected EOF).
 *
 * All other exceptions propagate immediately without retry, including:
 *
 * - {@see Exception\RequestException}: invalid request (no URL).
 * - {@see Exception\ProtocolException}: malformed server response.
 * - {@see Exception\TooManyRedirectsException}: redirect limit exceeded.
 * - {@see \Psl\Async\Exception\CancelledException}: cancellation token fired.
 *
 * ## Backoff strategy
 *
 * The delay between attempts grows exponentially using the formula:
 *
 *     delay = backoff * multiplier^(attempt - 1)
 *
 * With the default settings (backoff = 100ms, multiplier = 2), the delays are:
 * 100ms, 200ms, 400ms, and so on. The backoff sleep respects the cancellation token,
 * so cancelled requests do not wait for the backoff period to expire.
 *
 * ## Maximum attempts
 *
 * The {@see $maxAttempts} parameter controls the total number of attempts, including
 * the initial request. For example, with maxAttempts = 3, the client makes at most
 * 1 initial request + 2 retries. If all attempts fail, the exception from the last
 * attempt is propagated.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.2.2 Idempotent Methods
 *
 * @api
 */
final readonly class RetryClient implements ClientInterface
{
    private const array IDEMPOTENT_METHODS = [
        Message\METHOD_GET => true,
        Message\METHOD_HEAD => true,
        Message\METHOD_PUT => true,
        Message\METHOD_DELETE => true,
        Message\METHOD_OPTIONS => true,
        Message\METHOD_TRACE => true,
    ];

    private Duration $backoff;

    /**
     * Create a new retry client.
     *
     * @param ClientInterface $inner The inner client to delegate requests to. This is typically a {@see Client} or {@see RedirectClient} instance.
     * @param int<1, 10> $maxAttempts Maximum total number of attempts, including the initial request. Defaults to 3 (1 initial + 2 retries).
     * @param null|Duration $backoff Base delay before the first retry. Subsequent retries multiply this by the backoff multiplier. Defaults to 100ms when {@see null}.
     * @param int<1, max> $backoffMultiplier Exponential multiplier applied to the backoff duration after each failed attempt. Defaults to 2 (doubling).
     */
    public function __construct(
        private ClientInterface $inner,
        private int $maxAttempts = 3,
        null|Duration $backoff = null,
        private int $backoffMultiplier = 2,
    ) {
        $this->backoff = $backoff ?? Duration::milliseconds(100);
    }

    /**
     * Send an HTTP request, retrying on transport-level failure for idempotent methods.
     *
     * Delegates the request to the inner client. If the inner client throws a
     * transport-level exception and the request method is idempotent, the request
     * is retried after an exponential backoff delay. If the method is not idempotent
     * or all retry attempts are exhausted, the exception from the last attempt is
     * propagated.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.2.2 Idempotent Methods
     *
     * @inheritDoc
     */
    #[Override]
    public function send(
        Request $request,
        SendConfiguration $configuration = new SendConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                return $this->inner->send($request, $configuration, $cancellation);
            } catch (Network\Exception\RuntimeException|IO\Exception\RuntimeException $e) {
                if ($attempt >= $this->maxAttempts) {
                    throw $e;
                }

                if (!(self::IDEMPOTENT_METHODS[$request->method] ?? false)) {
                    throw $e;
                }

                $delayMs = (int) (
                    $this->backoff->getTotalMilliseconds()
                    * ($this->backoffMultiplier ** ($attempt - 1))
                );

                Async\sleep(Duration::milliseconds($delayMs), $cancellation);
            }
        }
    }
}
