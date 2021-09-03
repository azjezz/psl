<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use PhpBench\Attributes\BeforeMethods;

use Psl\Type\Exception\CoercionException;
use Psl\Type\TypeInterface;
use function Psl\Type\mixed;
use function Psl\Type\optional;
use function Psl\Type\shape;

#[BeforeMethods('__construct')]
final class ShapeTypeBench
{
    /**
     * @var TypeInterface<array>
     */
    private TypeInterface $emptyShape;
    /**
     * @var TypeInterface<array>
     */
    private TypeInterface $complexShapeWithOptionalValues;

    public function __construct()
    {
        $this->emptyShape                     = shape([], true);
        $this->complexShapeWithOptionalValues = shape([
            'foo' => mixed(),
            'bar' => mixed(),
            'baz' => mixed(),
            'tab' => optional(mixed()),
        ], true);
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
    public function benchEmptyShapeCoercionAgainstNonEmptyArray(): array
    {
        return $this->emptyShape->coerce(['foo' => 'bar']);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructure(): array
    {
        return $this->complexShapeWithOptionalValues->coerce([
            'foo' => null,
            'bar' => null,
            'baz' => null,
        ]);
    }

    /**
     * @throws CoercionException
     */
    public function benchComplexShapeCoercionAgainstValidStructureWithFurtherValues(): array
    {
        return $this->complexShapeWithOptionalValues->coerce([
            'foo' => null,
            'bar' => null,
            'baz' => null,
            'tab' => null,
            'taz' => null,
            'tar' => null,
            'waz' => null,
            'war' => null,
        ]);
    }
}
