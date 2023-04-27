<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use DateTimeImmutable;
use Psl\Type;
use RuntimeException;

final class ConvertedTypeTest extends TypeTest
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function getType(): Type\TypeInterface
    {
        return Type\converted(
            Type\string(),
            Type\instance_of(DateTimeImmutable::class),
            static fn (string $value): DateTimeImmutable =>
                DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $value)
                    ?: throw new RuntimeException('Unable to parse date format'),
        );
    }

    public function getValidCoercions(): iterable
    {
        yield ['2023-04-27 08:28:00', DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00')];
        yield [$this->stringable('2023-04-27 08:28:00'), DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00')];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [false];
        yield [''];
        yield ['2023-04-27'];
        yield ['2023-04-27 08:26'];
        yield ['27/04/2023'];
        yield [$this->stringable('2023-04-27')];
    }

    /**
     * @param DateTimeImmutable|mixed $a
     * @param DateTimeImmutable|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(DateTimeImmutable::class)->matches($a)) {
            $a = $a->format(self::DATE_FORMAT);
        }

        if (Type\instance_of(DateTimeImmutable::class)->matches($b)) {
            $b = $b->format(self::DATE_FORMAT);
        }

        return parent::equals($a, $b);
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), DateTimeImmutable::class];
    }
}
