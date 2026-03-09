# Tree

The `Tree` component provides immutable tree data structures and operations for hierarchical data manipulation.

Trees are fundamental data structures for representing hierarchical relationships. This component provides immutable tree nodes (`LeafNode` and `TreeNode`), pure constructor functions, functional operations (map, filter, reduce, fold), tree traversal algorithms, and search utilities.

## Design

The Tree component has a simple design:
- **`NodeInterface<T>`**: Base interface with `getValue(): T`
- **`LeafNode<T>`**: Leaf nodes (no children)
- **`TreeNode<T>`**: Tree nodes with `getChildren(): list<NodeInterface<T>>`

All tree operations are immutable -- they return new trees rather than modifying existing ones.

## Usage

### Building Trees

Use the `tree()` and `leaf()` constructor functions to build trees:

@example('collections/tree-building.php')

You can also build trees from nested arrays or flat lists with parent references, which is especially useful for database records:

@example('collections/tree-from-data.php')

### Transforming Trees

@example('collections/tree-transforming.php')

### Traversal

Three traversal orders are available:

@example('collections/tree-traversal.php')

### Searching and Paths

@example('collections/tree-searching.php')

### Custom Serialization

The `traverse()` function gives you full control over how the tree is serialized:

@example('collections/tree-serialization.php')

See `src/Psl/Tree/` for the full API.
