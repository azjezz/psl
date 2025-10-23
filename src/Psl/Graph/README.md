# Graph

The `Graph` component provides immutable graph data structures and algorithms for working with directed and undirected graphs.

## Introduction

Graphs are fundamental data structures for modeling relationships and networks. This component provides:
- Immutable directed and undirected graphs
- Pure constructor functions: `directed()` and `undirected()`
- Support for weighted and unweighted edges
- Graph traversal algorithms: BFS, DFS
- Path finding: shortest path (Dijkstra/BFS)
- Topological sorting and cycle detection
- Connectivity analysis

## Design

The Graph component uses an adjacency list representation:
- **`DirectedGraph<TNode, TWeight>`**: Directed graph with edges from node A to node B
- **`UndirectedGraph<TNode, TWeight>`**: Undirected graph with bidirectional edges
- **`Edge<TNode, TWeight>`**: Represents an edge with optional weight
- **Immutable operations**: All operations return new graphs
- **Flexible node types**: Nodes can be any type (objects, arrays, scalars, resources, etc.)

## Usage

```php
use Psl\Graph;

// Create a directed graph
$graph = Graph\directed();
$graph = Graph\add_node($graph, 'A');
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'B', 'C');

// Traverse the graph
$dfsOrder = Graph\dfs($graph, 'A');  // ['A', 'B', 'C']
$bfsOrder = Graph\bfs($graph, 'A');  // ['A', 'B', 'C']

// Find the shortest path
$path = Graph\shortest_path($graph, 'A', 'C'); // ['A', 'B', 'C']

// Check for cycles
$hasCycle = Graph\has_cycle($graph); // false

// Topological sort (for DAGs)
$sorted = Graph\topological_sort($graph); // ['A', 'B', 'C']

// Weighted graphs
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B', 5);  // edge with weight 5
$graph = Graph\add_edge($graph, 'A', 'C', 10); // edge with weight 10
$graph = Graph\add_edge($graph, 'B', 'C', 2);  // edge with weight 2

$path = Graph\shortest_path($graph, 'A', 'C'); // ['A', 'B', 'C'] (cost: 7)

// Undirected graphs
$graph = Graph\undirected();
$graph = Graph\add_edge($graph, 'A', 'B'); // adds edges in both directions
```

## Use Cases

- **Dependency Resolution**: Topological sort for build systems, package managers
- **Route Finding**: Shortest path for navigation, network routing
- **Social Networks**: Friend relationships, recommendations
- **State Machines**: Transitions between states
- **Data Flow**: Pipeline dependencies, task scheduling

## API

### Constructor Functions

---

#### [`directed(): DirectedGraph`](directed.php)

Creates an empty directed graph.

```php
$graph = Graph\directed();
```

---

#### [`undirected(): UndirectedGraph`](undirected.php)

Creates an empty undirected graph.

```php
$graph = Graph\undirected();
```

---

### Basic Operations

---

#### [`add_node(DirectedGraph|UndirectedGraph $graph, mixed $node): DirectedGraph|UndirectedGraph`](add_node.php)

Adds a node to the graph. Returns a new graph with the node added.

```php
$graph = Graph\directed();
$graph = Graph\add_node($graph, 'A');
```

---

#### [`add_edge(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to, mixed $weight = null): DirectedGraph|UndirectedGraph`](add_edge.php)

Adds an edge to the graph. For undirected graphs, adds edges in both directions.
Both nodes are automatically added if they don't exist.

```php
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');        // unweighted edge
$graph = Graph\add_edge($graph, 'A', 'C', 5);     // weighted edge
```

---

### Query Operations

---

#### [`nodes(DirectedGraph|UndirectedGraph $graph): list`](nodes.php)

Returns all nodes in the graph.

```php
$allNodes = Graph\nodes($graph); // ['A', 'B', 'C']
```

---

#### [`neighbors(DirectedGraph|UndirectedGraph $graph, mixed $node): list`](neighbors.php)

Returns all neighbor nodes of a given node.

```php
$neighbors = Graph\neighbors($graph, 'A'); // ['B', 'C']
```

---

### Traversal Algorithms

---

#### [`bfs(DirectedGraph|UndirectedGraph $graph, mixed $start): list`](bfs.php)

Performs breadth-first search starting from a given node.
Returns nodes in the order they are visited.

```php
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'A', 'C');
$graph = Graph\add_edge($graph, 'B', 'D');

$order = Graph\bfs($graph, 'A'); // ['A', 'B', 'C', 'D']
```

---

#### [`dfs(DirectedGraph|UndirectedGraph $graph, mixed $start): list`](dfs.php)

Performs depth-first search starting from a given node.
Returns nodes in the order they are visited.

```php
$order = Graph\dfs($graph, 'A'); // ['A', 'B', 'D', 'C']
```

---

### Path Algorithms

---

#### [`shortest_path(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to): list|null`](shortest_path.php)

Finds the shortest path between two nodes with integer weights.

For unweighted graphs, uses BFS.
For weighted graphs, uses Dijkstra's algorithm.

Returns the path as a list of nodes, or null if no path exists.

```php
$path = Graph\shortest_path($graph, 'A', 'C'); // ['A', 'B', 'C']
```

---

#### [`shortest_path_by(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to, Closure $weight_converter): list|null`](shortest_path_by.php)

Finds the shortest path between two nodes with custom weight types.

Accepts any weight type (float, string, objects, etc.) and a closure to convert weights to integer priorities.
This allows precise control over weight-to-priority conversion, avoiding issues with float-to-int casting.

Returns the path as a list of nodes, or null if no path exists.

```php
// Float weights with high precision
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B', 1.5);
$graph = Graph\add_edge($graph, 'B', 'C', 2.3);
$path = Graph\shortest_path_by($graph, 'A', 'C', fn($w) => (int)($w * 1000));

// String weights with custom mapping
$graph = Graph\add_edge($graph, 'A', 'B', 'low');
$graph = Graph\add_edge($graph, 'B', 'C', 'high');
$path = Graph\shortest_path_by($graph, 'A', 'C', fn($w) => $weightMap[$w]);
```

---

#### [`has_path(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to): bool`](has_path.php)

Checks if there is a path from one node to another.

```php
$exists = Graph\has_path($graph, 'A', 'C'); // true
```

---

### Topological Algorithms

---

#### [`topological_sort(DirectedGraph $graph): list|null`](topological_sort.php)

Performs topological sort on a directed acyclic graph (DAG).

Returns nodes in topological order, where for every directed edge (u, v), u comes before v.
Returns null if the graph contains a cycle.

```php
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'A', 'C');
$graph = Graph\add_edge($graph, 'B', 'D');
$graph = Graph\add_edge($graph, 'C', 'D');

$sorted = Graph\topological_sort($graph); // ['A', 'B', 'C', 'D'] or ['A', 'C', 'B', 'D']
```

---

#### [`has_cycle(DirectedGraph|UndirectedGraph $graph): bool`](has_cycle.php)

Checks if the graph contains a cycle.

For directed graphs, uses DFS with recursion stack.
For undirected graphs, uses DFS with parent tracking.

```php
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'A', 'B');
$graph = Graph\add_edge($graph, 'B', 'C');
$graph = Graph\add_edge($graph, 'C', 'A');

$hasCycle = Graph\has_cycle($graph); // true
```

---

### Classes

---

#### `DirectedGraph<TNode, TWeight>`

Immutable directed graph using adjacency list representation.

**Note:** Do not instantiate directly. Use `Graph\directed()` to create instances.

**Methods:**
- `getNodes(): list<TNode>` - Returns all nodes
- `getEdgesFrom(TNode $from): list<Edge<TNode, TWeight>>` - Returns edges from a node
- `hasNode(TNode $node): bool` - Checks if node exists
- `hasEdge(TNode $from, TNode $to): bool` - Checks if edge exists

---

#### `UndirectedGraph<TNode, TWeight>`

Immutable undirected graph using adjacency list representation.

**Note:** Do not instantiate directly. Use `Graph\undirected()` to create instances.

**Methods:**
- `getNodes(): list<TNode>` - Returns all nodes
- `getEdgesFrom(TNode $from): list<Edge<TNode, TWeight>>` - Returns edges from a node
- `hasNode(TNode $node): bool` - Checks if node exists
- `hasEdge(TNode $node1, TNode $node2): bool` - Checks if edge exists

---

#### `Edge<TNode, TWeight>`

Represents an edge in a graph.

**Properties:**
- `TNode $to` - The destination node
- `TWeight|null $weight` - Optional edge weight

---

## Examples

### Dependency Resolution

```php
use Psl\Graph;

// Build order for software packages
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'libc', 'gcc');
$graph = Graph\add_edge($graph, 'gcc', 'app');
$graph = Graph\add_edge($graph, 'libc', 'app');

$buildOrder = Graph\topological_sort($graph);
// ['libc', 'gcc', 'app']
```

### Social Network

```php
use Psl\Graph;

// Friend relationships
$graph = Graph\undirected();
$graph = Graph\add_edge($graph, 'Alice', 'Bob');
$graph = Graph\add_edge($graph, 'Bob', 'Charlie');
$graph = Graph\add_edge($graph, 'Alice', 'David');

// Find if two people are connected
$connected = Graph\has_path($graph, 'Alice', 'Charlie'); // true

// Find shortest connection path
$path = Graph\shortest_path($graph, 'Alice', 'Charlie');
// ['Alice', 'Bob', 'Charlie']
```

### Route Finding

```php
use Psl\Graph;

// City connections with distances
$graph = Graph\directed();
$graph = Graph\add_edge($graph, 'NYC', 'Boston', 215);
$graph = Graph\add_edge($graph, 'NYC', 'Philadelphia', 95);
$graph = Graph\add_edge($graph, 'Philadelphia', 'Boston', 310);

$shortestRoute = Graph\shortest_path($graph, 'NYC', 'Boston');
// ['NYC', 'Boston'] (cost: 215, shorter than via Philadelphia)
```

### Object Nodes

```php
use Psl\Graph;

// Task scheduling with object nodes
class Task {
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

// You can use any type as nodes: objects, arrays, resources, etc.
```

---
