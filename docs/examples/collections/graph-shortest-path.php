<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

/** @var Graph\DirectedGraph<string, int> $graph */
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'NYC', 'Boston', 215);
$graph = Graph\add_edge($graph, 'NYC', 'Philadelphia', 95);
$graph = Graph\add_edge($graph, 'Philadelphia', 'Boston', 310);

$path = Graph\shortest_path($graph, 'NYC', 'Boston');
// ['NYC', 'Boston'] (cost: 215, shorter than via Philadelphia)

/**
 * For non-integer weights, use shortest_path_by with a converter
 *
 * @var Graph\DirectedGraph<string, float> $graph
 */
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B', 1.5);
$graph = Graph\add_edge($graph, 'B', 'C', 2.3);
$path = Graph\shortest_path_by($graph, 'A', 'C', fn(float $w): int => (int) ($w * 1000));
