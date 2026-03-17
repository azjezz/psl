<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Graph;
use Psl\Str;
use Psl\Vec;

/**
 * Validate the monorepo dependency graph.
 *
 * Returns true if no circular dependencies are found.
 *
 * @param list<Package> $packages
 *
 * @mago-expect lint:excessive-nesting
 */
function check(array $packages): bool
{
    /** @var Graph\DirectedGraph<string, null> $graph */
    $graph = Graph\directed();

    foreach ($packages as $package) {
        $graph = Graph\add_node($graph, $package->name);
    }

    foreach ($packages as $package) {
        foreach ($package->dependencies as $dep) {
            $graph = Graph\add_edge($graph, $package->name, $dep);
        }
    }

    if (!Graph\has_cycle($graph)) {
        Log\success('No circular dependencies');

        return true;
    }

    Log\error('Circular dependencies detected');

    // Find and report cycles using DFS
    $visited = [];
    $stack = [];

    $dfs = function (string $node) use ($graph, &$visited, &$stack, &$dfs): void {
        $visited[$node] = true;
        $stack[$node] = true;

        foreach ($graph->getEdgesFrom($node) as $edge) {
            $neighbor = $edge->to;

            if (isset($stack[$neighbor])) {
                $path = [];
                $collecting = false;
                foreach ($stack as $n => $_) {
                    if ($n === $neighbor) {
                        $collecting = true;
                    }

                    if (!$collecting) {
                        continue;
                    }

                    $path[] = $n;
                }

                $path[] = $neighbor;

                $formatted = Str\join(
                    Vec\map($path, static fn(string $p): string => Log\styled(
                        $p,
                        Ansi\foreground(Color\bright_yellow()),
                        Style\bold(),
                    )),
                    Log\styled(' -> ', Ansi\foreground(Color\bright_red())),
                );

                Log\detail('%s', $formatted);

                return;
            }

            if (!isset($visited[$neighbor])) {
                $dfs($neighbor);
            }
        }

        unset($stack[$node]);
    };

    foreach (Graph\nodes($graph) as $node) {
        if (isset($visited[$node])) {
            continue;
        }

        $dfs($node);
    }

    return false;
}
