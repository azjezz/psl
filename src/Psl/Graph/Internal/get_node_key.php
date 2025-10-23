<?php

declare(strict_types=1);

namespace Psl\Graph\Internal;

use function get_resource_id;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;
use function md5;
use function serialize;
use function spl_object_id;

/**
 * Generates a unique string key for a node of any type.
 *
 * @return non-empty-string
 *
 * @internal
 *
 * @pure
 */
function get_node_key(mixed $node): string
{
    if (is_object($node)) {
        return 'o:' . spl_object_id($node);
    }

    if (is_array($node)) {
        return 'a:' . md5(serialize($node));
    }

    if (is_resource($node)) {
        return 'r:' . get_resource_id($node);
    }

    if (is_bool($node)) {
        return $node ? 'b:1' : 'b:0';
    }

    if (is_int($node)) {
        return 'i:' . $node;
    }

    if (is_float($node)) {
        return 'f:' . (string) $node;
    }

    if (is_string($node)) {
        return 's:' . $node;
    }

    if ($node === null) {
        return 'n:null';
    }

    return 't:' . md5(serialize($node));
}
