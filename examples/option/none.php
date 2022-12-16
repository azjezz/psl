<?php declare(strict_types=1);

use Psl\IO;
use Psl\Option;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * @return Option\Option<string>
 */
function get_some_string(): Option\Option
{
    return Option\some('some string');
}

/**
 * @return Option\Option<string>
 */
function get_none_string(): Option\Option
{
    return Option\none();
}

$some = get_some_string();
$none = get_none_string();

Psl\invariant($some->isSome(), 'Expected $some to be some.');
Psl\invariant($none === Option\NONE, 'Expected $none to be none.');

IO\write_line("OK");
