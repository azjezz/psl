# Tree

The `Tree` component provides immutable tree data structures and operations for hierarchical data manipulation.

## Introduction

Trees are fundamental data structures for representing hierarchical relationships. This component provides:
- Immutable tree nodes: `LeafNode` (no children) and `TreeNode` (with children)
- Pure constructor functions: `tree()` and `leaf()`
- Functional operations: map, filter, reduce, fold
- Tree traversal algorithms: pre-order, post-order, level-order
- Search and utility functions

## Design

The Tree component has a simple design:
- **`NodeInterface<T>`**: Base interface with `getValue(): T`
- **`LeafNode<T>`**: Leaf nodes (no children)
- **`TreeNode<T>`**: Tree nodes with `getChildren(): list<NodeInterface<T>>`

## Usage

```php
use Psl\Tree;

// Create a simple tree
$tree = Tree\tree('root', [
    Tree\leaf('child1'),
    Tree\leaf('child2'),
    Tree\tree('child3', [
        Tree\leaf('grandchild1'),
    ]),
]);

// Transform all values
$doubled = Tree\map($tree, fn($x) => $x . '!');
// Result: tree('root!', [leaf('child1!'), leaf('child2!'), tree('child3!', [leaf('grandchild1!')])])

// Filter nodes by predicate
$filtered = Tree\filter($tree, fn($x) => str_starts_with($x, 'child'));
// Result: null (root doesn't match)

// Traverse the tree
$values = Tree\pre_order($tree);
// Result: ['root', 'child1', 'child2', 'child3', 'grandchild1']

// Count nodes
$count = Tree\count($tree);
// Result: 5

// Find a value
$path = Tree\path_to($tree, fn($x) => $x === 'grandchild1');
// Result: ['root', 'child3', 'grandchild1']
```

## API

### Constructor Functions

---

#### [`tree(mixed $value, list<NodeInterface> $children = []): TreeNode`](tree.php)

Creates a tree node with a value and optional children.

```php
use Psl\Tree;

$tree = Tree\tree(1, [
    Tree\leaf(2),
    Tree\leaf(3),
]);
```

---

#### [`leaf(mixed $value): LeafNode`](leaf.php)

Creates a leaf node (a node with no children).

```php
use Psl\Tree;

$leaf = Tree\leaf(42);
```

---

### Transformation Functions

---

#### [`map(NodeInterface $tree, Closure $function): NodeInterface`](map.php)

Applies a function to all node values in the tree, returning a new tree.
Maps leaf nodes to leaf nodes and tree nodes to tree nodes, preserving structure.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$result = Tree\map($tree, fn($x) => $x * 2);
// Result: tree(2, [leaf(4), leaf(6)])
```

---

#### [`filter(NodeInterface $tree, Closure $predicate): TreeNode|null`](filter.php)

Filters tree nodes based on a predicate. Returns `null` if the root doesn't match.
If a parent is removed, its children are also removed.

```php
use Psl\Tree;

$tree = Tree\tree(2, [Tree\leaf(1), Tree\leaf(3)]);
$result = Tree\filter($tree, fn($x) => $x >= 2);
// Result: tree(2, [leaf(3)])
```

---

#### [`reduce(NodeInterface $tree, Closure $function, mixed $initial): mixed`](reduce.php)

Reduces the tree to a single value using pre-order traversal.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$sum = Tree\reduce($tree, fn($acc, $x) => $acc + $x, 0);
// Result: 6
```

---

#### [`fold(NodeInterface $tree, Closure $function): mixed`](fold.php)

Folds the tree using post-order traversal, passing children results to the function.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$result = Tree\fold($tree, fn($value, $children) => $value + array_sum($children));
// Result: 6
```

---

### Traversal Functions

---

#### [`pre_order(NodeInterface $tree): list`](pre_order.php)

Returns all values in pre-order (root, then children).

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\tree(2, [Tree\leaf(3)]), Tree\leaf(4)]);
$values = Tree\pre_order($tree);
// Result: [1, 2, 3, 4]
```

---

#### [`post_order(NodeInterface $tree): list`](post_order.php)

Returns all values in post-order (children, then root).

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\tree(2, [Tree\leaf(3)]), Tree\leaf(4)]);
$values = Tree\post_order($tree);
// Result: [3, 2, 4, 1]
```

---

#### [`level_order(NodeInterface $tree): list`](level_order.php)

Returns all values in level-order (breadth-first traversal).

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\tree(2, [Tree\leaf(3)]), Tree\leaf(4)]);
$values = Tree\level_order($tree);
// Result: [1, 2, 4, 3]
```

---

### Search Functions

---

#### [`find(NodeInterface $tree, Closure $predicate): mixed`](find.php)

Finds and returns the first value matching the predicate, or `null` if not found.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$result = Tree\find($tree, fn($x) => $x > 1);
// Result: 2
```

---

#### [`any(NodeInterface $tree, Closure $predicate): bool`](any.php)

Returns `true` if any value in the tree matches the predicate.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$hasEven = Tree\any($tree, fn($x) => $x % 2 === 0);
// Result: true
```

---

#### [`all(NodeInterface $tree, Closure $predicate): bool`](all.php)

Returns `true` if all values in the tree match the predicate.

```php
use Psl\Tree;

$tree = Tree\tree(2, [Tree\leaf(4), Tree\leaf(6)]);
$allEven = Tree\all($tree, fn($x) => $x % 2 === 0);
// Result: true
```

---

#### [`contains(NodeInterface $tree, mixed $value): bool`](contains.php)

Returns `true` if the tree contains the value (using strict comparison).

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);
$hasTwo = Tree\contains($tree, 2);
// Result: true
```

---

### Utility Functions

---

#### [`count(NodeInterface $tree): positive-int`](count.php)

Returns the total number of nodes in the tree.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\tree(3, [Tree\leaf(4)])]);
$nodeCount = Tree\count($tree);
// Result: 4
```

---

#### [`depth(NodeInterface $tree): int<0, max>`](depth.php)

Returns the maximum depth of the tree (0 for a leaf node).

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\tree(2, [Tree\leaf(3)])]);
$maxDepth = Tree\depth($tree);
// Result: 2
```

---

#### [`is_leaf(NodeInterface $tree): bool`](is_leaf.php)

Returns `true` if the node has no children.

```php
use Psl\Tree;

Tree\is_leaf(Tree\leaf(1));      // true
Tree\is_leaf(Tree\tree(1, []));  // true
Tree\is_leaf(Tree\tree(1, [Tree\leaf(2)])); // false
```

---

#### [`leaves(NodeInterface $tree): list`](leaves.php)

Returns all leaf node values in the tree.

```php
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\tree(3, [Tree\leaf(4)])]);
$leafValues = Tree\leaves($tree);
// Result: [2, 4]
```

---

### Path Functions

---

#### [`path_to(NodeInterface $tree, Closure $predicate): list|null`](path_to.php)

Finds the path from root to the first node matching the predicate.
Returns a list of values from root to target (inclusive), or `null` if not found.

```php
use Psl\Tree;

$tree = Tree\tree('a', [
    Tree\tree('b', [Tree\leaf('c')]),
    Tree\leaf('d'),
]);
$path = Tree\path_to($tree, fn($x) => $x === 'c');
// Result: ['a', 'b', 'c']
```

---

#### [`to_index(NodeInterface $tree, Closure $predicate): list<int>|null`](to_index.php)

Finds the index path from root to the first node matching the predicate.
Returns a list of child indices from root to target, or `null` if not found.

```php
use Psl\Tree;

$tree = Tree\tree('a', [
    Tree\tree('b', [Tree\leaf('c')]),
    Tree\leaf('d'),
]);
$indexPath = Tree\to_index($tree, fn($x) => $x === 'c');
// Result: [0, 0]

Tree\to_index($tree, fn($x) => $x === 'a');  // []
Tree\to_index($tree, fn($x) => $x === 'd');  // [1]
Tree\to_index($tree, fn($x) => $x === 'z');  // null
```

---

#### [`at_index(NodeInterface $tree, list<int> $index_path): mixed`](at_index.php)

Gets the value at the specified index path (list of child indices).
An empty index path returns the root value. Returns `null` if the index path is invalid.

```php
use Psl\Tree;

$tree = Tree\tree('a', [
    Tree\tree('b', [Tree\leaf('c')]),
    Tree\leaf('d'),
]);
Tree\at_index($tree, []);        // 'a'
Tree\at_index($tree, [0]);       // 'b'
Tree\at_index($tree, [0, 0]);    // 'c'
Tree\at_index($tree, [1]);       // 'd'
Tree\at_index($tree, [5]);       // null
```

---

### Serialization Functions

---

#### [`traverse(NodeInterface $tree, Closure $transform): mixed`](traverse.php)

Traverses the tree and transforms each node using a custom function.
This allows custom serialization with control over children representation.

The transform function receives:
- The current node's value
- A callable that returns the transformed children when invoked

```php
use Psl\Tree;

// Custom JSON structure with specific children property name
$tree = Tree\tree(['id' => 1, 'label' => 'A'], [
    Tree\leaf(['id' => 2, 'label' => 'B']),
    Tree\leaf(['id' => 3, 'label' => 'C'])
]);

$result = Tree\traverse(
    $tree,
    fn($value, $traverse) => [
        'id' => $value['id'],
        'label' => $value['label'],
        'items' => $traverse()  // Custom children property name
    ]
);
// Result: [
//     'id' => 1,
//     'label' => 'A',
//     'items' => [
//         ['id' => 2, 'label' => 'B', 'items' => []],
//         ['id' => 3, 'label' => 'C', 'items' => []]
//     ]
// ]

// Conditional children rendering
$result = Tree\traverse(
    $tree,
    fn($value, $traverse) => [
        'name' => $value,
        'children' => $traverse(),
        'hasChildren' => count($traverse()) > 0
    ]
);

// Custom string representation
$result = Tree\traverse(
    Tree\tree('root', [Tree\leaf('a'), Tree\leaf('b')]),
    function ($value, $traverse) {
        $children = $traverse();
        return empty($children) ? $value : $value . '(' . implode(',', $children) . ')';
    }
);
// Result: 'root(a,b)'
```

---

### Conversion Functions

---

#### [`from_array(array $array): TreeNode`](from_array.php)

Creates a tree from a nested array structure.

```php
use Psl\Tree;

$tree = Tree\from_array([
    'value' => 'root',
    'children' => [
        ['value' => 'child1', 'children' => []],
        ['value' => 'child2', 'children' => []],
    ],
]);
```

---

#### [`from_list(list $items, Closure $get_id, Closure $get_parent_id, Closure $get_value): NodeInterface`](from_list.php)

Builds a tree from a flat list of items with parent references.

This is especially useful for building trees from database records that use parent_id relationships.

```php
use Psl\Tree;

// Database records with parent_id relationship
$records = [
    ['id' => 1, 'name' => 'Root', 'parent_id' => null],
    ['id' => 2, 'name' => 'Child A', 'parent_id' => 1],
    ['id' => 3, 'name' => 'Child B', 'parent_id' => 1],
    ['id' => 4, 'name' => 'Grandchild', 'parent_id' => 2],
];

$tree = Tree\from_list(
    $records,
    fn($record) => $record['id'],        // Get node ID
    fn($record) => $record['parent_id'], // Get parent ID (null for root)
    fn($record) => $record['name']       // Extract value
);

// You can also keep the full record or transform it
$tree = Tree\from_list(
    $records,
    fn($r) => $r['id'],
    fn($r) => $r['parent_id'],
    fn($r) => $r  // Keep full record as node value
);
```

---

#### [`to_array(NodeInterface $tree): array`](to_array.php)

Converts a tree to a nested array structure.

```php
use Psl\Tree;

$tree = Tree\tree('root', [
    Tree\leaf('child1'),
    Tree\leaf('child2'),
]);
$array = Tree\to_array($tree);
// Result: [
//     'value' => 'root',
//     'children' => [
//         ['value' => 'child1', 'children' => []],
//         ['value' => 'child2', 'children' => []],
//     ],
// ]
```

---

### Interfaces

---

#### `NodeInterface<T>`

Base interface for all tree nodes.

**Methods:**
- `getValue(): T` - Returns the node's value

**Implementations:**
- `LeafNode<T>` - Leaf nodes with no children
- `TreeNode<T>` - Tree nodes with children (has additional `getChildren(): list<NodeInterface<T>>` method)

---

### Classes

---

#### `LeafNode<T>`

Immutable leaf node implementation.

```php
use Psl\Tree;

$leaf = new Tree\LeafNode(42);
// or use the constructor function:
$leaf = Tree\leaf(42);
```

---

#### `TreeNode<T>`

Immutable tree node implementation with optional children.

```php
use Psl\Tree;

$tree = new Tree\TreeNode('root', [
    new Tree\LeafNode('child'),
]);
// or use the constructor function:
$tree = Tree\tree('root', [Tree\leaf('child')]);
```

---
