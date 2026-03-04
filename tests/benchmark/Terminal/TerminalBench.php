<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Terminal;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Internal\CsiKeyMap;
use Psl\Terminal\Internal\EventParser;
use Psl\Terminal\Internal\SgrMouseParser;
use Psl\Terminal\Layout\Constraint;
use Psl\Terminal\Rect;

use function Psl\Terminal\Layout\Internal\solve;

#[Groups(['terminal'])]
final class TerminalBench
{
    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideEventParserData')]
    public function benchEventParserFeed(array $params): void
    {
        $parser = new EventParser();
        $parser->feed($params['data']);
    }

    /**
     * @return iterable<string, array{data: string}>
     */
    public function provideEventParserData(): iterable
    {
        yield 'printable_short' => ['data' => 'hello'];
        yield 'printable_long' => ['data' => str_repeat('abcdefghij', 10)];
        yield 'arrow_keys' => ['data' => "\e[A\e[B\e[C\e[D\e[A\e[B\e[C\e[D"];
        yield 'csi_mixed' => ['data' => "\e[1;5A\e[3~\e[15~\e[H\e[F\e[5;2~"];
        yield 'sgr_mouse' => ['data' => "\e[<0;10;20M\e[<0;10;20m\e[<32;15;25M"];
        yield 'paste_short' => ['data' => "\e[200~hello world\e[201~"];
        yield 'paste_long' => ['data' => "\e[200~" . str_repeat('paste content ', 50) . "\e[201~"];
        yield 'utf8' => ['data' => 'héllo wörld àçé ñ ü'];
    }

    public function benchCsiKeyMapSimple(): void
    {
        CsiKeyMap::map('', 'A');
        CsiKeyMap::map('', 'B');
        CsiKeyMap::map('', 'C');
        CsiKeyMap::map('', 'D');
        CsiKeyMap::map('', 'H');
        CsiKeyMap::map('', 'F');
    }

    public function benchCsiKeyMapTilde(): void
    {
        CsiKeyMap::map('1', '~');
        CsiKeyMap::map('3', '~');
        CsiKeyMap::map('5', '~');
        CsiKeyMap::map('15', '~');
        CsiKeyMap::map('5;5', '~');
    }

    public function benchCsiKeyMapModified(): void
    {
        CsiKeyMap::map('1;5', 'A');
        CsiKeyMap::map('1;2', 'D');
        CsiKeyMap::map('1;3', 'C');
        CsiKeyMap::map('97;5', 'u');
    }

    public function benchSgrMouseParser(): void
    {
        SgrMouseParser::parse('0;10;20', false);
        SgrMouseParser::parse('0;10;20', true);
        SgrMouseParser::parse('32;15;25', false);
        SgrMouseParser::parse('64;5;10', false);
    }

    /**
     * @param array{width: int, height: int} $params
     */
    #[ParamProviders('provideBufferSizes')]
    public function benchBufferCreate(array $params): void
    {
        new Buffer($params['width'], $params['height']);
    }

    /**
     * @param array{width: int, height: int} $params
     */
    #[ParamProviders('provideBufferSizes')]
    public function benchBufferFill(array $params): void
    {
        $buffer = new Buffer($params['width'], $params['height']);
        $cell = new Cell('X', []);
        $buffer->fill($cell);
    }

    /**
     * @param array{width: int, height: int} $params
     */
    #[ParamProviders('provideBufferSizes')]
    public function benchBufferResize(array $params): void
    {
        $buffer = new Buffer(10, 10);
        $buffer->resize($params['width'], $params['height']);
    }

    /**
     * @return iterable<string, array{width: int, height: int}>
     */
    public function provideBufferSizes(): iterable
    {
        yield 'small_24x80' => ['width' => 80, 'height' => 24];
        yield 'medium_50x120' => ['width' => 120, 'height' => 50];
        yield 'large_100x200' => ['width' => 200, 'height' => 100];
    }

    /**
     * @param array{constraints: list<Constraint>} $params
     */
    #[ParamProviders('provideLayoutData')]
    public function benchLayoutSolve(array $params): void
    {
        $rect = new Rect(0, 0, 200, 100);
        solve($rect, $params['constraints'], false);
    }

    /**
     * @return iterable<string, array{constraints: list<Constraint>}>
     */
    public function provideLayoutData(): iterable
    {
        yield 'few_fixed' => ['constraints' => [
            Constraint::fixed(20),
            Constraint::fixed(30),
            Constraint::fixed(50),
        ]];
        yield 'few_mixed' => ['constraints' => [
            Constraint::fixed(20),
            Constraint::fill(),
            Constraint::fixed(30),
        ]];
        yield 'many_mixed' => ['constraints' => [
            Constraint::fixed(10),
            Constraint::fill(),
            Constraint::fixed(10),
            Constraint::fill(),
            Constraint::fixed(10),
            Constraint::fill(),
            Constraint::fixed(10),
            Constraint::fill(),
            Constraint::fixed(10),
            Constraint::fill(),
        ]];
    }
}
