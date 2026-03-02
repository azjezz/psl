# Graph

The `Graph` component provides immutable graph data structures and algorithms for working with directed and undirected graphs.

Graphs are fundamental data structures for modeling relationships and networks. This component supports directed and undirected graphs, weighted and unweighted edges, traversal (BFS, DFS), shortest path finding, topological sorting, and cycle detection. All operations are immutable and return new graph instances.

## Design

The Graph component uses an adjacency list representation:
- **`DirectedGraph<TNode, TWeight>`**: Directed graph with edges from node A to node B
- **`UndirectedGraph<TNode, TWeight>`**: Undirected graph with bidirectional edges
- **`Edge<TNode, TWeight>`**: Represents an edge with optional weight
- **Flexible node types**: Nodes can be any type (objects, arrays, scalars, resources, etc.)

## Usage

### Building Graphs

@example('collections/graph-building.php')

### Traversal

@example('collections/graph-traversal.php')

### Shortest Path

For unweighted graphs, BFS is used. For weighted graphs, Dijkstra's algorithm is used.

@example('collections/graph-shortest-path.php')

### Cycle Detection and Topological Sort

@example('collections/graph-cycle-detection.php')

### Object Nodes

Nodes can be any type, including objects:

@example('collections/graph-object-nodes.php')

## Use Cases

- **Dependency Resolution**: Topological sort for build systems, package managers
- **Route Finding**: Shortest path for navigation, network routing
- **Social Networks**: Friend relationships, recommendations
- **State Machines**: Transitions between states
- **Data Flow**: Pipeline dependencies, task scheduling

See `src/Psl/Graph/` for the full API.
