<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

function get_untrusted_input(): mixed
{
    return '<some string>';
}

// Coerce: convert a string-like value into a non-empty-string
$trustedInput = Type\non_empty_string()->coerce(get_untrusted_input());

// Assert: verify it is already a non-empty-string (no conversion)
$trustedInput = Type\non_empty_string()->assert(get_untrusted_input());

// Match: check without throwing
$isTrustworthy = Type\non_empty_string()->matches(get_untrusted_input());
