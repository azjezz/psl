<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

// Topological sort for DAGs (dependency ordering)
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'libc', 'gcc');
$graph = Graph\add_edge($graph, 'gcc', 'app');
$graph = Graph\add_edge($graph, 'libc', 'app');

$buildOrder = Graph\topological_sort($graph);
// ['libc', 'gcc', 'app']

// Cycle detection
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'B', 'C');
$graph = Graph\add_edge($graph, 'C', 'A');

Graph\has_cycle($graph); // true
