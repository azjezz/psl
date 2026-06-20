<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

$graph = Graph\directed::<string, int>();
$graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
$graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
$graph = Graph\add_edge::<string, int>($graph, 'B', 'D');

Graph\bfs::<string, int>($graph, 'A'); // ['A', 'B', 'C', 'D'] -- breadth-first
Graph\dfs::<string, int>($graph, 'A'); // ['A', 'B', 'D', 'C'] -- depth-first
