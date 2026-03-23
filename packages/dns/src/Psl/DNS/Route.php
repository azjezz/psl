<?php

declare(strict_types=1);

namespace Psl\DNS;

/**
 * A route mapping a set of domains to a specific resolver.
 *
 * Used by {@see SplitHorizonResolver} to direct queries for matching
 * domains to the designated resolver.
 *
 * @api
 */
final readonly class Route
{
    /**
     * @param list<string> $domains The domain suffixes this route matches against.
     * @param ResolverInterface $resolver The resolver to use for matching domains.
     */
    public function __construct(
        public array $domains,
        public ResolverInterface $resolver,
    ) {}
}
