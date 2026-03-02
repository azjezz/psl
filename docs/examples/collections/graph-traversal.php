<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'A', 'C');
$graph = Graph\add_edge($graph, 'B', 'D');

Graph\bfs($graph, 'A'); // ['A', 'B', 'C', 'D'] -- breadth-first
Graph\dfs($graph, 'A'); // ['A', 'B', 'D', 'C'] -- depth-first
