<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

// Topological sort for DAGs (dependency ordering)
$graph = Graph\directed::<string, int>();
$graph = Graph\add_edge::<string, int>($graph, 'libc', 'gcc');
$graph = Graph\add_edge::<string, int>($graph, 'gcc', 'app');
$graph = Graph\add_edge::<string, int>($graph, 'libc', 'app');

$buildOrder = Graph\topological_sort::<string, int>($graph);
// ['libc', 'gcc', 'app']

// Cycle detection
$graph = Graph\directed::<string, int>();
$graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
$graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
$graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

Graph\has_cycle::<string, int>($graph); // true
