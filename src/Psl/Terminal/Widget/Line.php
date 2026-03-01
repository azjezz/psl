<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

/**
 * A line of styled text composed of spans.
 *
 * @immutable
 */
final readonly class Line
{
    /**
     * @param list<Span> $spans
     */
    private function __construct(
        public array $spans,
    ) {}

    /**
     * Create a line from a list of spans.
     *
     * @param list<Span> $spans
     */
    public static function new(array $spans): self
    {
        return new self($spans);
    }

    /**
     * Create an empty line.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Returns the total display width of all spans in this line.
     */
    public function width(): int
    {
        $width = 0;
        foreach ($this->spans as $span) {
            $width += $span->width();
        }

        return $width;
    }
}
