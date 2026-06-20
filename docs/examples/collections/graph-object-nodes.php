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

$graph = Graph\directed::<Task, int>();
$compile = new Task('compile', 5);
$test = new Task('test', 3);
$deploy = new Task('deploy', 2);

$graph = Graph\add_edge::<Task, int>($graph, $compile, $test);
$graph = Graph\add_edge::<Task, int>($graph, $test, $deploy);

$executionOrder = Graph\topological_sort::<Task, int>($graph);

// [$compile, $test, $deploy]
