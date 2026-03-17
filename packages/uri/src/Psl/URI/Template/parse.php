<?php

declare(strict_types=1);

namespace Psl\URI\Template;

use Psl\URI\Exception\InvalidTemplateException;
use Psl\URI\Internal\TemplateParser;

/**
 * Parse a URI Template string per RFC 6570.
 *
 * Supports all four levels:
 * - Level 1: Simple {var}
 * - Level 2: Reserved {+var}, Fragment {#var}
 * - Level 3: Label {.var}, Path {/var}, Parameter {;var}, Query {?var}, Continuation {&var}
 * - Level 4: Prefix {var:3}, Explode {var*}
 *
 * @throws InvalidTemplateException If the template syntax is invalid.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6570
 */
function parse(string $template): Template
{
    $parts = TemplateParser::parse($template);

    return new Template($template, $parts);
}
