<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\DNS\Exception\NetworkException;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Exception\RuntimeException as DnsRuntimeException;
use Psl\DNS\RacingResolver;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\IP\Address;
use RuntimeException;
use Throwable;

final class RacingResolverTest extends TestCase
{
    public function testReturnsFirstSuccessfulResponse(): void
    {
        $fast = new Response(1, ResponseCode::NoError, [], [], []);
        $slow = new Response(2, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createDelayedResolver($fast, Duration::milliseconds(1)),
            $this->createDelayedResolver($slow, Duration::seconds(5)),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($fast, $result);
    }

    public function testReturnsSecondResolverWhenFirstFails(): void
    {
        $response = new Response(3, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'first failed',
                new RuntimeException('test'),
            )),
            $this->createMockResolver($response),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($response, $result);
    }

    public function testReturnsSecondResolverWhenFirstTimesOut(): void
    {
        $response = new Response(4, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'timeout',
                new RuntimeException('test'),
            )),
            $this->createMockResolver($response),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($response, $result);
    }

    public function testReturnsSecondResolverWhenFirstReturnsInvalidResponse(): void
    {
        $response = new Response(5, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createThrowingResolver(ProtocolException::forQueryPacket()),
            $this->createMockResolver($response),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($response, $result);
    }

    public function testSkipsServerFailureResponse(): void
    {
        $success = new Response(6, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createMockResolver(new Response(7, ResponseCode::ServerFailure, [], [], [])),
            $this->createMockResolver($success),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($success, $result);
    }

    public function testSkipsServerRefusedResponse(): void
    {
        $success = new Response(8, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([
            $this->createMockResolver(new Response(9, ResponseCode::ServerRefused, [], [], [])),
            $this->createMockResolver($success),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame($success, $result);
    }

    public function testReturnsNonExistentDomainWithoutSkipping(): void
    {
        $nxResponse = new Response(10, ResponseCode::NonExistentDomain, [], [], []);

        $resolver = new RacingResolver([
            $this->createMockResolver($nxResponse),
            $this->createDelayedResolver(new Response(11, ResponseCode::NoError, [], [], []), Duration::seconds(5)),
        ]);

        $result = $resolver->query('no-such.example', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $result->code);
    }

    public function testReturnsFormatErrorWithoutSkipping(): void
    {
        $response = new Response(12, ResponseCode::FormatError, [], [], []);

        $resolver = new RacingResolver([
            $this->createMockResolver($response),
            $this->createDelayedResolver(new Response(13, ResponseCode::NoError, [], [], []), Duration::seconds(5)),
        ]);

        $result = $resolver->query('example.com', RecordType::A);

        static::assertSame(ResponseCode::FormatError, $result->code);
    }

    public function testThrowsWhenAllResolversFail(): void
    {
        $resolver = new RacingResolver([
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'first',
                new RuntimeException('test'),
            )),
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'timeout',
                new RuntimeException('test'),
            )),
        ]);

        $this->expectException(DnsRuntimeException::class);
        $this->expectExceptionMessage('All racing resolvers failed.');

        $resolver->query('example.com', RecordType::A);
    }

    public function testThrowsWithLastErrorAsPrevious(): void
    {
        $resolver = new RacingResolver([
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'first',
                new RuntimeException('test'),
            )),
            $this->createThrowingResolver(NetworkException::forQueryFailed(
                'UDP',
                'timeout',
                new RuntimeException('test'),
            )),
        ]);

        try {
            $resolver->query('example.com', RecordType::A);
            static::fail('Expected DnsRuntimeException');
        } catch (DnsRuntimeException $e) {
            static::assertSame('All racing resolvers failed.', $e->getMessage());
            static::assertNotNull($e->getPrevious());
        }
    }

    public function testThrowsWhenAllServersReturnErrors(): void
    {
        $resolver = new RacingResolver([
            $this->createMockResolver(new Response(14, ResponseCode::ServerFailure, [], [], [])),
            $this->createMockResolver(new Response(15, ResponseCode::ServerRefused, [], [], [])),
        ]);

        try {
            $resolver->query('example.com', RecordType::A);
            static::fail('Expected DnsRuntimeException');
        } catch (DnsRuntimeException $e) {
            static::assertSame('All racing resolvers failed.', $e->getMessage());
            $previous = $e->getPrevious();
            static::assertInstanceOf(DnsRuntimeException::class, $previous);
            static::assertSame('DNS server returned ServerRefused.', $previous->getMessage());
        }
    }

    public function testReverseQueryDelegatesToQuery(): void
    {
        $response = new Response(16, ResponseCode::NoError, [], [], []);

        $resolver = new RacingResolver([$this->createMockResolver($response)]);
        $result = $resolver->reverseQuery(Address::v4('192.168.1.1'));

        static::assertSame($response, $result);
    }

    private function createMockResolver(Response $response): ResolverInterface
    {
        return new class($response) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly Response $response,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return $this->response;
            }
        };
    }

    private function createThrowingResolver(Throwable $exception): ResolverInterface
    {
        return new class($exception) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly Throwable $exception,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                throw $this->exception;
            }
        };
    }

    private function createDelayedResolver(Response $response, Duration $delay): ResolverInterface
    {
        return new class($response, $delay) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private readonly Response $response,
                private readonly Duration $delay,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                Async\sleep($this->delay);

                return $this->response;
            }
        };
    }
}
