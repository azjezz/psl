<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use Override;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\HTTP\Client\ClientInterface;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\RetryClient;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network\Exception\RuntimeException;
use Psl\URL;

final class RetryClientTest extends TestCase
{
    /** @param non-empty-uppercase-string $method */
    private static function request(string $method = 'GET'): Request
    {
        return new Request(method: $method, url: URL\parse('http://example.com/'));
    }

    private static function failThenSucceedClient(int $failures): ClientInterface
    {
        return new class($failures) implements ClientInterface {
            public int $attempts = 0;

            public function __construct(
                private int $failures,
            ) {}

            #[Override]
            public function send(
                Request $request,
                SendConfiguration $configuration = new SendConfiguration(),
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $this->attempts++;
                if ($this->attempts <= $this->failures) {
                    throw new RuntimeException('connection refused');
                }

                return new Transaction(
                    [],
                    null,
                    new Response(status: 200, headers: FieldMap::default(), body: new IO\MemoryHandle('ok')),
                );
            }
        };
    }

    private static function alwaysFailClient(): ClientInterface
    {
        return new class() implements ClientInterface {
            public int $attempts = 0;

            #[Override]
            public function send(
                Request $request,
                SendConfiguration $configuration = new SendConfiguration(),
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $this->attempts++;
                throw new RuntimeException('connection refused');
            }
        };
    }

    public function testNoRetryOnSuccess(): void
    {
        $inner = self::failThenSucceedClient(0);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $transaction = $client->send(self::request());

        static::assertSame(200, $transaction->response->status);
        static::assertSame(1, $inner->attempts);
    }

    public function testRetriesOnNetworkError(): void
    {
        $inner = self::failThenSucceedClient(2);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $transaction = $client->send(self::request());

        static::assertSame(200, $transaction->response->status);
        static::assertSame(3, $inner->attempts);
    }

    public function testExhaustsAttemptsThenThrows(): void
    {
        $inner = self::alwaysFailClient();
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $this->expectException(RuntimeException::class);

        try {
            $client->send(self::request());
        } finally {
            static::assertSame(3, $inner->attempts);
        }
    }

    public function testSingleAttemptNoRetry(): void
    {
        $inner = self::alwaysFailClient();
        $client = new RetryClient($inner, maxAttempts: 1, backoff: Duration::milliseconds(1));

        $this->expectException(RuntimeException::class);

        try {
            $client->send(self::request());
        } finally {
            static::assertSame(1, $inner->attempts);
        }
    }

    public function testDoesNotRetryPost(): void
    {
        $inner = self::alwaysFailClient();
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $this->expectException(RuntimeException::class);

        try {
            $client->send(self::request('POST'));
        } finally {
            static::assertSame(1, $inner->attempts);
        }
    }

    public function testDoesNotRetryPatch(): void
    {
        $inner = self::alwaysFailClient();
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $this->expectException(RuntimeException::class);

        try {
            $client->send(self::request('PATCH'));
        } finally {
            static::assertSame(1, $inner->attempts);
        }
    }

    public function testRetriesGet(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('GET'));

        static::assertSame(200, $tx->response->status);
    }

    public function testRetriesPut(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('PUT'));

        static::assertSame(200, $tx->response->status);
    }

    public function testRetriesDelete(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('DELETE'));

        static::assertSame(200, $tx->response->status);
    }

    public function testRetriesHead(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('HEAD'));

        static::assertSame(200, $tx->response->status);
    }

    public function testRetriesOptions(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('OPTIONS'));

        static::assertSame(200, $tx->response->status);
    }

    public function testRetriesTrace(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $tx = $client->send(self::request('TRACE'));

        static::assertSame(200, $tx->response->status);
    }

    public function testDoesNotRetryOnProtocolException(): void
    {
        $inner = new class() implements ClientInterface {
            public int $attempts = 0;

            #[Override]
            public function send(
                Request $request,
                SendConfiguration $configuration = new SendConfiguration(),
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $this->attempts++;
                throw ProtocolException::forMalformedResponse('bad');
            }
        };

        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1));

        $this->expectException(ProtocolException::class);

        try {
            $client->send(self::request());
        } finally {
            static::assertSame(1, $inner->attempts);
        }
    }

    public function testExponentialBackoffTiming(): void
    {
        $inner = self::failThenSucceedClient(2);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(50), backoffMultiplier: 2);

        $start = Timestamp::monotonic();
        $client->send(self::request());
        $elapsed = Timestamp::monotonic()->since($start)->getTotalMilliseconds();

        static::assertGreaterThanOrEqual(120, $elapsed);
    }

    public function testMultiplierOneGivesConstantDelay(): void
    {
        $inner = self::failThenSucceedClient(2);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(50), backoffMultiplier: 1);

        $start = Timestamp::monotonic();
        $client->send(self::request());
        $elapsed = Timestamp::monotonic()->since($start)->getTotalMilliseconds();

        static::assertGreaterThanOrEqual(80, $elapsed);
        static::assertLessThan(500, $elapsed);
    }

    public function testDefaultBackoffIsApplied(): void
    {
        $inner = self::failThenSucceedClient(1);
        $client = new RetryClient($inner, maxAttempts: 3);

        $start = Timestamp::monotonic();
        $client->send(self::request());
        $elapsed = Timestamp::monotonic()->since($start)->getTotalMilliseconds();

        static::assertGreaterThanOrEqual(80, $elapsed);
    }

    public function testMinimalBackoff(): void
    {
        $inner = self::failThenSucceedClient(2);
        $client = new RetryClient($inner, maxAttempts: 3, backoff: Duration::milliseconds(1), backoffMultiplier: 1);

        $start = Timestamp::monotonic();
        $client->send(self::request());
        $elapsed = Timestamp::monotonic()->since($start)->getTotalMilliseconds();

        static::assertLessThan(50, $elapsed);
    }
}
