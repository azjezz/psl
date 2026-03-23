<?php

declare(strict_types=1);

namespace Psl\DNS;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;

use function array_key_last;
use function array_map;

/**
 * A resolver that dispatches a query to multiple resolvers concurrently
 * and returns the first successful response, ignoring the rest.
 *
 * @api
 */
final readonly class RacingResolver implements ResolverInterface
{
    use ResolverConvenienceMethodsTrait;

    /**
     * @param non-empty-list<ResolverInterface> $resolvers Resolvers to race concurrently.
     */
    public function __construct(
        private array $resolvers,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function query(
        string $name,
        RecordType $type,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
        array $ednsOptions = [],
    ): Response {
        $awaitables = array_map(
            static fn(ResolverInterface $resolver): Async\Awaitable => Async\run(static function () use (
                $resolver,
                $name,
                $type,
                $cancellation,
                $ednsOptions,
            ): Response {
                $response = $resolver->query($name, $type, $cancellation, $ednsOptions);

                if (
                    $response->code === ResponseCode::ServerFailure
                    || $response->code === ResponseCode::ServerRefused
                ) {
                    throw Exception\RuntimeException::forServerError($response->code->name);
                }

                return $response;
            }),
            $this->resolvers,
        );

        try {
            return Async\any($awaitables);
        } catch (Async\Exception\CompositeException $e) {
            $reasons = $e->getReasons();

            throw Exception\RuntimeException::forAllResolversFailed('racing', $reasons[array_key_last($reasons)]);
        }
    }
}
