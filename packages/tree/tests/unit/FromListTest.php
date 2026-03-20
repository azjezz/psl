<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

use function strtolower;

final class FromListTest extends TestCase
{
    public function testFromListWithSimpleHierarchy(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Root', 'parent_id' => null],
            ['id' => 2, 'name' => 'Child A', 'parent_id' => 1],
            ['id' => 3, 'name' => 'Child B', 'parent_id' => 1],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['name'],
        );

        static::assertSame('Root', $tree->getValue());
        static::assertCount(2, $tree->getChildren());
        static::assertSame('Child A', $tree->getChildren()[0]->getValue());
        static::assertSame('Child B', $tree->getChildren()[1]->getValue());
    }

    public function testFromListWithDeepHierarchy(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Root', 'parent_id' => null],
            ['id' => 2, 'name' => 'Child', 'parent_id' => 1],
            ['id' => 3, 'name' => 'Grandchild', 'parent_id' => 2],
            ['id' => 4, 'name' => 'Great-grandchild', 'parent_id' => 3],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['name'],
        );

        static::assertSame('Root', $tree->getValue());
        $child = $tree->getChildren()[0];
        static::assertSame('Child', $child->getValue());
        $grandchild = $child->getChildren()[0];
        static::assertSame('Grandchild', $grandchild->getValue());
        $greatGrandchild = $grandchild->getChildren()[0];
        static::assertSame('Great-grandchild', $greatGrandchild->getValue());
    }

    public function testFromListPreservesFullRecords(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Root', 'data' => 'root-data', 'parent_id' => null],
            ['id' => 2, 'name' => 'Child', 'data' => 'child-data', 'parent_id' => 1],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): array => $r, // Keep full record
        );

        static::assertSame(
            ['id' => 1, 'name' => 'Root', 'data' => 'root-data', 'parent_id' => null],
            $tree->getValue(),
        );
        static::assertSame(
            ['id' => 2, 'name' => 'Child', 'data' => 'child-data', 'parent_id' => 1],
            $tree->getChildren()[0]->getValue(),
        );
    }

    public function testFromListWithLeafNodes(): void
    {
        $records = [
            ['id' => 1, 'name' => 'Root', 'parent_id' => null],
            ['id' => 2, 'name' => 'Leaf', 'parent_id' => 1],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['name'],
        );

        $leaf = $tree->getChildren()[0];
        static::assertTrue(Tree\is_leaf($leaf));
    }

    public function testFromListWithIntegerIds(): void
    {
        $records = [
            ['id' => 100, 'value' => 'A', 'parent_id' => null],
            ['id' => 200, 'value' => 'B', 'parent_id' => 100],
            ['id' => 300, 'value' => 'C', 'parent_id' => 100],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['value'],
        );

        static::assertSame('A', $tree->getValue());
        static::assertCount(2, $tree->getChildren());
    }

    public function testFromListWithStringIds(): void
    {
        $records = [
            ['id' => 'root-uuid', 'label' => 'Root', 'parent_id' => null],
            ['id' => 'child-uuid', 'label' => 'Child', 'parent_id' => 'root-uuid'],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): string => $r['id'],
            static fn(array $r): null|string => $r['parent_id'],
            static fn(array $r): string => $r['label'],
        );

        static::assertSame('Root', $tree->getValue());
        static::assertSame('Child', $tree->getChildren()[0]->getValue());
    }

    public function testFromListWithMultipleChildrenPerLevel(): void
    {
        $records = [
            ['id' => 1, 'val' => 'A', 'parent_id' => null],
            ['id' => 2, 'val' => 'B', 'parent_id' => 1],
            ['id' => 3, 'val' => 'C', 'parent_id' => 1],
            ['id' => 4, 'val' => 'D', 'parent_id' => 1],
            ['id' => 5, 'val' => 'E', 'parent_id' => 2],
            ['id' => 6, 'val' => 'F', 'parent_id' => 2],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['val'],
        );

        static::assertCount(3, $tree->getChildren());
        static::assertSame('B', $tree->getChildren()[0]->getValue());
        static::assertSame('C', $tree->getChildren()[1]->getValue());
        static::assertSame('D', $tree->getChildren()[2]->getValue());
        static::assertCount(2, $tree->getChildren()[0]->getChildren());
    }

    public function testFromListThrowsOnNoRootNode(): void
    {
        $this->expectException(Tree\Exception\NoRootNodeException::class);
        $this->expectExceptionMessage('No root item found');

        $records = [
            ['id' => 1, 'parent_id' => 2], // No null parent_id
            ['id' => 2, 'parent_id' => 3],
        ];

        Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): int => $r['parent_id'],
            static fn(array $r): array => $r,
        );
    }

    public function testFromListThrowsOnMultipleRoots(): void
    {
        $this->expectException(Tree\Exception\MultipleRootNodesException::class);
        $this->expectExceptionMessage('Multiple root items found');

        $records = [
            ['id' => 1, 'parent_id' => null],
            ['id' => 2, 'parent_id' => null], // Two roots!
        ];

        Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null => $r['parent_id'],
            static fn(array $r): array => $r,
        );
    }

    public function testFromListThrowsOnOrphanedNode(): void
    {
        $this->expectException(Tree\Exception\OrphanedNodeException::class);
        $this->expectExceptionMessage("references non-existent parent_id '999'");

        $records = [
            ['id' => 1, 'name' => 'Root', 'parent_id' => null],
            ['id' => 2, 'name' => 'Orphan', 'parent_id' => 999], // parent 999 doesn't exist
        ];

        Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['name'],
        );
    }

    public function testFromListWithValueTransformation(): void
    {
        $records = [
            ['id' => 1, 'title' => 'ROOT', 'parent_id' => null],
            ['id' => 2, 'title' => 'CHILD', 'parent_id' => 1],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => strtolower($r['title']), // Transform to lowercase
        );

        static::assertSame('root', $tree->getValue());
        static::assertSame('child', $tree->getChildren()[0]->getValue());
    }

    public function testFromListWithComplexObjects(): void
    {
        $obj1 = (object) ['id' => 'a', 'name' => 'Object A', 'parent_id' => null];
        $obj2 = (object) ['id' => 'b', 'name' => 'Object B', 'parent_id' => 'a'];

        $tree = Tree\from_list(
            [$obj1, $obj2],
            static fn(object $o): string => $o->id,
            static fn(object $o): null|string => $o->parent_id,
            static fn(object $o): string => $o->name,
        );

        static::assertSame('Object A', $tree->getValue());
        static::assertSame('Object B', $tree->getChildren()[0]->getValue());
    }

    public function testFromListOrderMaintained(): void
    {
        // Test that children appear in the same order as in the input list
        $records = [
            ['id' => 1, 'name' => 'Root', 'parent_id' => null],
            ['id' => 2, 'name' => 'First', 'parent_id' => 1],
            ['id' => 3, 'name' => 'Second', 'parent_id' => 1],
            ['id' => 4, 'name' => 'Third', 'parent_id' => 1],
        ];

        $tree = Tree\from_list(
            $records,
            static fn(array $r): int => $r['id'],
            static fn(array $r): null|int => $r['parent_id'],
            static fn(array $r): string => $r['name'],
        );

        $children = $tree->getChildren();
        static::assertSame('First', $children[0]->getValue());
        static::assertSame('Second', $children[1]->getValue());
        static::assertSame('Third', $children[2]->getValue());
    }
}
