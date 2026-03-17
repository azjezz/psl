<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Str;
use Psl\Str\Byte;

#[Groups(['str'])]
final class StrBench
{
    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchTrim(array $params): void
    {
        Str\trim($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchTrimCustomMask(array $params): void
    {
        Str\trim($params['data'], 'abc');
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchTrimLeft(array $params): void
    {
        Str\trim_left($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchTrimRight(array $params): void
    {
        Str\trim_right($params['data']);
    }

    /**
     * @param array{data: string, delimiter: string} $params
     */
    #[ParamProviders('provideSplitData')]
    public function benchSplit(array $params): void
    {
        Str\split($params['data'], $params['delimiter']);
    }

    /**
     * @param array{data: string, needle: string, replacement: string} $params
     */
    #[ParamProviders('provideReplaceData')]
    public function benchReplace(array $params): void
    {
        Str\replace($params['data'], $params['needle'], $params['replacement']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchContains(array $params): void
    {
        Str\contains($params['data'], 'needle');
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchStartsWith(array $params): void
    {
        Str\starts_with($params['data'], '  hello');
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchEndsWith(array $params): void
    {
        Str\ends_with($params['data'], 'world  ');
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchCapitalize(array $params): void
    {
        Str\capitalize($params['data']);
    }

    /**
     * @param array{data: string, replacements: array<string, string>} $params
     */
    #[ParamProviders('provideByteReplaceEveryData')]
    public function benchByteReplaceEvery(array $params): void
    {
        Str\Byte\replace_every($params['data'], $params['replacements']);
    }

    /**
     * @param array{data: string, length: non-negative-int, pad: non-empty-string} $params
     */
    #[ParamProviders('providePadData')]
    public function benchPadLeft(array $params): void
    {
        Str\pad_left($params['data'], $params['length'], $params['pad']);
    }

    /**
     * @param array{data: string, length: non-negative-int, pad: non-empty-string} $params
     */
    #[ParamProviders('providePadData')]
    public function benchPadRight(array $params): void
    {
        Str\pad_right($params['data'], $params['length'], $params['pad']);
    }

    /**
     * @param array{data: string, replacements: array<string, string>} $params
     */
    #[ParamProviders('provideReplaceEveryData')]
    public function benchReplaceEvery(array $params): void
    {
        Str\replace_every($params['data'], $params['replacements']);
    }

    /**
     * @param array{data: string, replacements: array<string, string>} $params
     */
    #[ParamProviders('provideReplaceEveryData')]
    public function benchReplaceEveryCi(array $params): void
    {
        Str\replace_every_ci($params['data'], $params['replacements']);
    }

    /**
     * @param array{data: string, replacements: array<string, string>} $params
     */
    #[ParamProviders('provideReplaceEveryData')]
    public function benchByteReplaceEveryCi(array $params): void
    {
        Str\Byte\replace_every_ci($params['data'], $params['replacements']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchUppercase(array $params): void
    {
        $_ = Str\uppercase($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideStrings')]
    public function benchLowercase(array $params): void
    {
        $_ = Str\lowercase($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: string}>
     */
    public function provideStrings(): iterable
    {
        yield 'short' => ['data' => '  hello world  '];
        yield 'medium' => ['data' => str_repeat('  hello world  ', 10)];
        yield 'long' => ['data' => str_repeat('  hello world  ', 100)];
    }

    /**
     * @return iterable<non-empty-string, array{data: string, delimiter: string}>
     */
    public function provideSplitData(): iterable
    {
        yield 'single char, short' => ['data' => 'a,b,c,d,e', 'delimiter' => ','];
        yield 'single char, long' => ['data' => implode(',', range(1, 100)), 'delimiter' => ','];
        yield 'multi char, short' => ['data' => 'a::b::c::d::e', 'delimiter' => '::'];
        yield 'multi char, long' => ['data' => implode('::', range(1, 100)), 'delimiter' => '::'];
    }

    /**
     * @return iterable<non-empty-string, array{data: string, needle: string, replacement: string}>
     */
    public function provideReplaceData(): iterable
    {
        yield 'present, short' => ['data' => 'hello world', 'needle' => 'world', 'replacement' => 'earth'];
        yield 'absent, short' => ['data' => 'hello world', 'needle' => 'xyz', 'replacement' => 'abc'];
        yield 'present, long' => [
            'data' => str_repeat('hello world ', 100),
            'needle' => 'world',
            'replacement' => 'earth',
        ];
        yield 'absent, long' => ['data' => str_repeat('hello world ', 100), 'needle' => 'xyz', 'replacement' => 'abc'];
    }

    /**
     * @return iterable<non-empty-string, array{data: string, replacements: array<string, string>}>
     */
    public function provideByteReplaceEveryData(): iterable
    {
        yield 'few replacements' => [
            'data' => 'hello world foo bar',
            'replacements' => ['hello' => 'hi', 'world' => 'earth'],
        ];
        yield 'many replacements' => [
            'data' => str_repeat('hello world foo bar baz ', 20),
            'replacements' => ['hello' => 'hi', 'world' => 'earth', 'foo' => 'qux', 'bar' => 'quux', 'baz' => 'corge'],
        ];
    }

    /**
     * @return iterable<non-empty-string, array{data: string, length: int, pad: string}>
     */
    public function providePadData(): iterable
    {
        yield 'short, space' => ['data' => 'hello', 'length' => 20, 'pad' => ' '];
        yield 'short, multi' => ['data' => 'hello', 'length' => 20, 'pad' => '-='];
        yield 'long, space' => ['data' => 'hello', 'length' => 200, 'pad' => ' '];
    }

    /**
     * @return iterable<non-empty-string, array{data: string, replacements: array<string, string>}>
     */
    public function provideReplaceEveryData(): iterable
    {
        yield 'few replacements' => [
            'data' => 'hello world foo bar',
            'replacements' => ['hello' => 'hi', 'world' => 'earth'],
        ];
        yield 'many replacements' => [
            'data' => str_repeat('hello world foo bar baz ', 20),
            'replacements' => ['hello' => 'hi', 'world' => 'earth', 'foo' => 'qux', 'bar' => 'quux', 'baz' => 'corge'],
        ];
    }
}
