<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

// Directed graph
$graph = Graph\directed::<string, int>();
$graph = Graph\add_node::<string, int>($graph, 'A');
$graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
$graph = Graph\add_edge::<string, int>($graph, 'B', 'C');

// Undirected graph (edges go in both directions)
$graph = Graph\undirected::<string, int>();
$graph = Graph\add_edge::<string, int>($graph, 'A', 'B'); // adds edges in both directions

// Weighted edges
$graph = Graph\directed::<string, int>();
$graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 5);
$graph = Graph\add_edge::<string, int>($graph, 'A', 'C', 10);
$graph = Graph\add_edge::<string, int>($graph, 'B', 'C', 2);
