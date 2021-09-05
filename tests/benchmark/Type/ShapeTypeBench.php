<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use PhpBench\Attributes\BeforeMethods;

use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;
use function Psl\Type\mixed;
use function Psl\Type\optional;
use function Psl\Type\shape;

#[BeforeMethods('__construct')]
final class ShapeTypeBench
{
    private const NON_EMPTY_ARRAY = ['foo' => 'bar'];
    private const VALID_STRUCTURE = [
        'foo' => null,
        'bar' => null,
        'baz' => null,
    ];
    private const VALID_STRUCTURE_WITH_FURTHER_VALUES = [
        'foo' => null,
        'bar' => null,
        'baz' => null,
        'tab' => null,
        'taz' => null,
        'tar' => null,
        'waz' => null,
        'war' => null,
    ];

    /**
     * @var TypeInterface<array>
     */
    private TypeInterface $emptyShape;
    /**
     * @var TypeInterface<array>
     */
    private TypeInterface $complexShapeWithOptionalValues;
    private iterable $emptyIterable;
    private iterable $nonEmptyIterable;
    private iterable $validStructureIterable;
    private iterable $validStructureWithFurtherValuesIterable;

    public function __construct()
    {
        $this->emptyShape                     = shape([], true);
        $this->complexShapeWithOptionalValues = shape([
            'foo' => mixed(),
            'bar' => mixed(),
            'baz' => mixed(),
            'tab' => optional(mixed()),
        ], true);
        $this->emptyIterable = new ArrayIterator([]);
        $this->nonEmptyIterable = new ArrayIterator(self::NON_EMPTY_ARRAY);
        $this->validStructureIterable = new ArrayIterator(self::VALID_STRUCTURE);
        $this->validStructureWithFurtherValuesIterable = new ArrayIterator(self::VALID_STRUCTURE_WITH_FURTHER_VALUES);
    }

    /**
     * @throws CoercionException
     */
    public function benchEmptyShapeCoercionAgainstEmptyArray(): array
    {
        return $this->emptyShape->coerce([]);
    }

    /**
     * @throws CoercionException
     */
    public function benchEmptyShapeCoercionAgainstEmptyIterable(): array
    {
        return $this->emptyShape->coerce($this->emptyIterable);
    }

    /**
     * @throws CoercionException
     */
    public function benchEmptyShapeCoercionAgainstNonEmptyArray(): array
    {
        return $this->emptyShape->coerce(self::NON_EMPTY_ARRAY);
    }

    /**
     * @throws CoercionException
     */
    public function benchEmptyShapeCoercionAgainstNonEmptyIterable(): array
    {
        return $this->emptyShape->coerce($this->nonEmptyIterable);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructure(): array
    {
        return $this->complexShapeWithOptionalValues->coerce(self::VALID_STRUCTURE);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructureIterable(): array
    {
        return $this->complexShapeWithOptionalValues->coerce($this->validStructureIterable);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructureWithFurtherValues(): array
    {
        return $this->complexShapeWithOptionalValues->coerce(self::VALID_STRUCTURE_WITH_FURTHER_VALUES);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructureWithFurtherValuesIterable(): array
    {
        return $this->complexShapeWithOptionalValues->coerce($this->validStructureWithFurtherValuesIterable);
    }
}
