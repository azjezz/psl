<?php

declare(strict_types=1);

namespace Psl\URI\Template;

use Psl\URI;
use Psl\URI\Exception;
use Psl\URI\Internal\TemplateExpander;
use Stringable;

/**
 * Represents a parsed URI Template per RFC 6570.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6570
 */
final readonly class Template implements Stringable
{
    /**
     * @param string $template The original template string.
     * @param list<string|array{operator: string, variables: list<array{name: string, modifier: string, prefix: null|int}>}> $parts Parsed template parts.
     */
    public function __construct(
        private string $template,
        private array $parts,
    ) {}

    /**
     * Expand the template with the given variables per RFC 6570.
     *
     * @param array<string, null|string|int|float|list<string>|array<string, string>> $variables
     *
     * @throws Exception\InvalidURIException If the expanded result is not a valid URI.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6570#section-3
     */
    public function expand(array $variables): URI\URI
    {
        $expanded = TemplateExpander::expand($this->parts, $variables);

        return URI\parse($expanded);
    }

    /**
     * Returns the original template string.
     */
    public function toString(): string
    {
        return $this->template;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
