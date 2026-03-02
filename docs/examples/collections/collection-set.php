<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Collection\MutableSet;
use Psl\Collection\Set;

/**
 * A set is a collection of unique values. It can be either mutable or immutable.
 *
 * @var Set<string> $tags
 */
$tags = Set::fromArray(['php', 'psl', 'php']); // duplicates removed
$tags->count(); // 2
$tags->contains('php'); // true
$tags->contains('rust'); // false

/**
 * Mutable set -- supports in-place modification
 *
 * @var MutableSet<string> $visited
 */
$visited = MutableSet::fromArray([]);
$visited->add('page-a');
$visited->add('page-b');
$visited->add('page-a'); // no-op, already present
$visited->count(); // 2
$visited->remove('page-a');
