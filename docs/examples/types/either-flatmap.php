<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

/**
 * @return Either\Either<string, int>
 */
function parse_int(string $input): Either\Either<string, int>
{
    return is_numeric($input) ? new Either\Right::<int>((int) $input) : new Either\Left::<string>("'{$input}' is not a number");
}

$result = new Either\Right::<string>('42')->flatMapRight::<string, int>(static fn(string $v): Either\Either<string, int> => parse_int($v));

// Right(42)
