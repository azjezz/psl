<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

// Directed graph
$graph = Graph\directed();
$graph = Graph\add_node($graph, 'A');
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'B', 'C');

// Undirected graph (edges go in both directions)
$graph = Graph\undirected();
$graph = Graph\add_edge($graph, 'A', 'B'); // adds edges in both directions

// Weighted edges
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B', 5);
$graph = Graph\add_edge($graph, 'A', 'C', 10);
$graph = Graph\add_edge($graph, 'B', 'C', 2);
