<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

/**
 * @return Either\Either<string, int>
 */
function parse_int(string $input): Either\Either
{
    return is_numeric($input) ? new Either\Right((int) $input) : new Either\Left("'{$input}' is not a number");
}

$result = new Either\Right('42')->flatMapRight(static fn(string $v): Either\Either => parse_int($v));

// Right(42)
