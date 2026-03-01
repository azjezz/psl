<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

/**
 * A single entry in a menu widget.
 *
 * @immutable
 */
final readonly class MenuItem
{
    /**
     * @param list<Span> $spans
     */
    private function __construct(
        public array $spans,
    ) {}

    /**
     * Create a menu item from a plain string.
     */
    public static function raw(string $content): self
    {
        return new self([Span::raw($content)]);
    }

    /**
     * Create a menu item from styled spans.
     *
     * @param list<Span> $spans
     */
    public static function styled(array $spans): self
    {
        return new self($spans);
    }
}
