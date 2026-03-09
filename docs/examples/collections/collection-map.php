<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Collection\Map;
use Psl\Collection\MutableMap;

/**
 * Immutable map
 *
 * @var Map<string, int> $scores
 */
$scores = Map::fromArray(['alice' => 95, 'bob' => 82, 'charlie' => 91]);
$scores->at('alice'); // 95
$scores->get('unknown'); // null (no exception)
$scores->contains('bob'); // true
$scores->keys(); // Vector<string>
$scores->values(); // Vector<int>

// Filter to passing scores
$passing = $scores->filter(fn(int $s): bool => $s >= 90);
$passing->toArray(); // ['alice' => 95, 'charlie' => 91]

/**
 * Mutable map
 *
 * @var MutableMap<string, string> $config
 */
$config = MutableMap::fromArray(['debug' => 'false']);
$config->add('version', '2.0'); // adds or overwrites
$config->set('debug', 'true'); // overwrites existing key (throws if missing)
$config->remove('debug');
