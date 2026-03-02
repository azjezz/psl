<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Graph;

class Task
{
    public function __construct(
        public readonly string $name,
        public readonly int $duration,
    ) {}
}

$graph = Graph\directed();
$compile = new Task('compile', 5);
$test = new Task('test', 3);
$deploy = new Task('deploy', 2);

$graph = Graph\add_edge($graph, $compile, $test);
$graph = Graph\add_edge($graph, $test, $deploy);

$executionOrder = Graph\topological_sort($graph);

// [$compile, $test, $deploy]
