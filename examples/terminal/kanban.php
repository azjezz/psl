<?php

declare(strict_types=1);

namespace Psl\Example\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\DateTime;
use Psl\Iter;
use Psl\Math;
use Psl\Str;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;
use Psl\Vec;

require __DIR__ . '/../../vendor/autoload.php';

enum Tag: string
{
    case Bug = 'bug';
    case Feature = 'feature';
    case Docs = 'docs';
    case Urgent = 'urgent';
    case Testing = 'testing';
    case Refactor = 'refactor';
}

final readonly class Card
{
    /**
     * @param list<Tag> $tags
     */
    public function __construct(
        public string $title,
        public array $tags = [],
    ) {}
}

/** @var list<string> */
const COLUMN_NAMES = ['Todo', 'In Progress', 'Done'];

final class KanbanState
{
    /** @var list<list<Card>> */
    public array $columns;
    /** @var non-negative-int */
    public int $active_col = 0;
    /** @var list<int> Selected card index per column */
    public array $selected = [0, 0, 0];
    /** @var list<int> Scroll offset per column */
    public array $col_scroll = [0, 0, 0];
    /** @var list<int> Visible card slots per column (computed during render) */
    public array $col_visible_slots = [1, 1, 1];
    /** @var list<Terminal\Rect> Column rects (computed during render) */
    public array $col_rects = [];
    public bool $input_mode = false;
    public string $input_text = '';

    public function __construct()
    {
        $this->columns = namespace\initial_columns();
    }
}

/**
 * @return array{Color\Color, null|Color\Color}
 */
function tag_style(Tag $tag): array
{
    return match ($tag) {
        Tag::Bug, Tag::Urgent => [Color\bright_red(), null],
        Tag::Feature => [Color\bright_blue(), null],
        Tag::Docs => [Color\bright_green(), null],
        Tag::Testing => [Color\bright_yellow(), null],
        Tag::Refactor => [Color\bright_magenta(), null],
    };
}

/**
 * @return list<list<Card>>
 */
function initial_columns(): array
{
    return [
        [
            new Card('Fix login bug', [Tag::Bug, Tag::Urgent]),
            new Card('Add unit tests', [Tag::Testing]),
            new Card('Write README', [Tag::Docs]),
            new Card('Rate limiting', [Tag::Feature]),
        ],
        [
            new Card('Update API docs', [Tag::Docs]),
            new Card('Refactor DB layer', [Tag::Refactor]),
        ],
        [
            new Card('Deploy v2.0', [Tag::Feature]),
            new Card('Fix typo in docs', [Tag::Docs]),
            new Card('Add CI pipeline', [Tag::Feature]),
        ],
    ];
}

function handle_input_key(Event\Key $event, KanbanState $state): void
{
    if ($event->is('escape')) {
        $state->input_mode = false;
        $state->input_text = '';
        return;
    }

    if ($event->is('enter') && Str\trim($state->input_text) !== '') {
        $title = Str\trim($state->input_text);
        $col = $state->active_col;
        $state->columns[$col][] = new Card($title);
        $state->selected[$col] = Iter\count($state->columns[$col]) - 1;
        $state->input_mode = false;
        $state->input_text = '';
        return;
    }

    if ($event->is('backspace')) {
        if ($state->input_text !== '') {
            /** @var non-negative-int $len */
            $len = Str\length($state->input_text) - 1;
            $state->input_text = Str\slice($state->input_text, 0, $len);
        }

        return;
    }

    if ($event->is('ctrl+u')) {
        $state->input_text = '';
        return;
    }

    if ($event->char !== null) {
        $state->input_text .= $event->char;
    }
}

/**
 * @param non-negative-int $fromCol
 * @param non-negative-int $toCol
 */
function move_card(KanbanState $state, int $fromCol, int $toCol): void
{
    /** @var non-negative-int $cardIdx */
    $cardIdx = $state->selected[$fromCol];
    $card = $state->columns[$fromCol][$cardIdx];

    /** @var non-negative-int $next */
    $next = $cardIdx + 1;
    $state->columns[$fromCol] = Vec\concat(
        Vec\slice($state->columns[$fromCol], 0, $cardIdx),
        Vec\slice($state->columns[$fromCol], $next),
    );
    $state->selected[$fromCol] = Math\minva(
        $state->selected[$fromCol],
        Math\maxva(0, Iter\count($state->columns[$fromCol]) - 1),
    );

    $state->columns[$toCol][] = $card;
    $state->active_col = $toCol;
    $state->selected[$toCol] = Iter\count($state->columns[$toCol]) - 1;
}

function handle_normal_key(Event\Key $event, KanbanState $state): void
{
    $col = $state->active_col;
    $cardCount = Iter\count($state->columns[$col]);

    if ($event->is('tab')) {
        $state->active_col = ($state->active_col + 1) % 3;
        return;
    }

    if ($event->is('shift+tab')) {
        $state->active_col = ($state->active_col + 2) % 3;
        return;
    }

    if ($event->is('up') && $cardCount > 0) {
        if ($state->selected[$col] <= 0) {
            return;
        }

        $state->selected[$col]--;
        if ($state->selected[$col] < $state->col_scroll[$col]) {
            $state->col_scroll[$col] = $state->selected[$col];
        }

        return;
    }

    if ($event->is('down') && $cardCount > 0) {
        if ($state->selected[$col] >= ($cardCount - 1)) {
            return;
        }

        $state->selected[$col]++;
        $visibleSlots = $state->col_visible_slots[$col] ?? 1;
        if ($state->selected[$col] >= ($state->col_scroll[$col] + $visibleSlots)) {
            $state->col_scroll[$col] = $state->selected[$col] - $visibleSlots + 1;
        }

        return;
    }

    if ($event->is('right') && $col < 2 && $cardCount > 0) {
        /** @var non-negative-int $targetCol */
        $targetCol = $col + 1;
        namespace\move_card($state, $col, $targetCol);
        return;
    }

    if ($event->is('left') && $col > 0 && $cardCount > 0) {
        $targetCol = $col - 1;
        namespace\move_card($state, $col, $targetCol);
        return;
    }

    if ($event->char === 'n') {
        $state->input_mode = true;
        $state->input_text = '';
        return;
    }

    if (($event->char === 'd' || $event->is('delete')) && $cardCount > 0) {
        /** @var non-negative-int $cardIdx */
        $cardIdx = $state->selected[$col];
        /** @var non-negative-int $next */
        $next = $cardIdx + 1;
        $state->columns[$col] = Vec\concat(
            Vec\slice($state->columns[$col], 0, $cardIdx),
            Vec\slice($state->columns[$col], $next),
        );
        $newCount = Iter\count($state->columns[$col]);
        $state->selected[$col] = $newCount > 0 ? Math\minva($state->selected[$col], $newCount - 1) : 0;

        return;
    }

    if ($event->is('home') && $cardCount > 0) {
        $state->selected[$col] = 0;
        $state->col_scroll[$col] = 0;
        return;
    }

    if ($event->is('end') && $cardCount > 0) {
        $state->selected[$col] = $cardCount - 1;
        $visibleSlots = $state->col_visible_slots[$col] ?? 1;
        $state->col_scroll[$col] = Math\maxva(0, $cardCount - $visibleSlots);
    }
}

/**
 * @param list<Card> $cards
 * @param non-negative-int $colIdx
 */
function render_column(
    Terminal\Rect $colRect,
    array $cards,
    int $colIdx,
    KanbanState $state,
    Terminal\Buffer $buffer,
): void {
    $isActiveCol = $colIdx === $state->active_col;
    $cardCount = Iter\count($cards);
    $colName = COLUMN_NAMES[$colIdx];

    $borderColor = $isActiveCol ? Color\bright_cyan() : Color\ansi256(240);
    $titleFg = $isActiveCol ? Color\bright_white() : Color\bright_black();

    $colBlock = Widget\Block::new()
        ->title(" {$colName} ({$cardCount}) ")
        ->titleStyle(Ansi\foreground($titleFg), Style\bold())
        ->border(Widget\Border::rounded(Ansi\foreground($borderColor)))
        ->padding(left: 1, right: 2);

    $colBlock->render($colRect, Widget\Paragraph::new([]), $buffer);
    $innerArea = $colBlock->innerArea($colRect);

    if ($innerArea->isEmpty()) {
        return;
    }

    $cardHeight = 4;
    $cardGap = 1;
    $slotHeight = $cardHeight + $cardGap;
    $visibleSlots = (int) ($innerArea->height / $slotHeight);
    if ($visibleSlots < 1) {
        $visibleSlots = 1;
    }

    $scrollOffset = $state->col_scroll[$colIdx];
    $maxScroll = Math\maxva(0, $cardCount - $visibleSlots);
    $scrollOffset = Math\minva($scrollOffset, $maxScroll);
    $state->col_scroll[$colIdx] = $scrollOffset;
    $state->col_visible_slots[$colIdx] = $visibleSlots;

    for ($slot = 0; $slot < $visibleSlots && ($scrollOffset + $slot) < $cardCount; $slot++) {
        /** @var non-negative-int $cardIdx */
        $cardIdx = $scrollOffset + $slot;
        $card = $cards[$cardIdx];
        $isSelected = $isActiveCol && $cardIdx === $state->selected[$colIdx];

        $cardY = $innerArea->y + ($slot * $slotHeight);
        $cardRect = new Terminal\Rect($innerArea->x, $cardY, $innerArea->width, $cardHeight);

        if ($cardRect->bottom() > $innerArea->bottom()) {
            break;
        }

        $cardBorderColor = $isSelected ? Color\bright_yellow() : Color\ansi256(240);
        $cardTitleFg = $isSelected ? Color\bright_white() : null;

        /** @var list<Widget\Span> $tagSpans */
        $tagSpans = [];
        foreach ($card->tags as $ti => $tag) {
            if ($ti > 0) {
                $tagSpans[] = Widget\Span::styled(' · ', Ansi\foreground(Color\ansi256(240)));
            }

            [$tagFg] = namespace\tag_style($tag);
            $tagSpans[] = Widget\Span::styled($tag->value, Ansi\foreground($tagFg));
        }

        if ($tagSpans === []) {
            $tagSpans[] = Widget\Span::raw('');
        }

        Widget\Block::new()->border(Widget\Border::rounded(Ansi\foreground($cardBorderColor)))->render(
            $cardRect,
            Widget\Paragraph::new([
                Widget\Line::new([Widget\Span::styled(
                    $card->title,
                    ...$cardTitleFg !== null ? [Ansi\foreground($cardTitleFg), Style\bold()] : [Style\bold()],
                )]),
                Widget\Line::new($tagSpans),
            ]),
            $buffer,
        );
    }

    if ($cardCount > $visibleSlots) {
        $scrollbarRect = new Terminal\Rect($colRect->right() - 2, $innerArea->y, 1, $innerArea->height);
        Widget\Scrollbar::new()
            ->contentLength($cardCount)
            ->viewportLength($visibleSlots)
            ->position($scrollOffset)
            ->thumbStyle(Ansi\foreground($isActiveCol ? Color\bright_cyan() : Color\ansi256(245)))
            ->trackStyle(Ansi\foreground(Color\ansi256(238)))
            ->render($scrollbarRect, $buffer);
    }

    if ($state->input_mode && $isActiveCol) {
        $inputY = $innerArea->y + (Math\minva($cardCount - $scrollOffset, $visibleSlots) * $slotHeight);
        $inputRect = new Terminal\Rect($innerArea->x, $inputY, $innerArea->width, $cardHeight);

        if ($inputRect->bottom() <= $innerArea->bottom()) {
            $inputBlock = Widget\Block::new()->border(Widget\Border::rounded(Ansi\foreground(Color\bright_green())));

            $inputBlock->render($inputRect, Widget\Paragraph::new([]), $buffer);
            $inputInner = $inputBlock->innerArea($inputRect);

            if ($inputInner->height >= 1) {
                $inputRow = new Terminal\Rect($inputInner->x, $inputInner->y, $inputInner->width, 1);
                Widget\TextInput::new()
                    ->value($state->input_text)
                    ->cursor(Str\length($state->input_text))
                    ->placeholder('Card title...')
                    ->style(Ansi\foreground(Color\bright_white()))
                    ->cursorStyle(Ansi\foreground(Color\bright_green()))
                    ->placeholderStyle(Ansi\foreground(Color\bright_black()))
                    ->render($inputRow, $buffer);
            }

            if ($inputInner->height >= 2) {
                $hintRow = new Terminal\Rect($inputInner->x, $inputInner->y + 1, $inputInner->width, 1);
                Widget\Paragraph::new([Widget\Line::new([
                    Widget\Span::styled('Enter: add · Esc: cancel', Ansi\foreground(Color\bright_black())),
                ])])->render($hintRow, $buffer);
            }
        }
    }
}

function render_status_bar(Terminal\Rect $statusBar, KanbanState $state, Terminal\Buffer $buffer): void
{
    if ($state->input_mode) {
        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled(
                ' Adding new card...  Type a title and press Enter',
                Ansi\foreground(Color\bright_green()),
            ),
        ])])->render($statusBar, $buffer);
        return;
    }

    $rightText = "Tab: column | \u{2191}/\u{2193} select | \u{2190}/\u{2192} move | n new | d delete | Ctrl+C quit ";
    $rightLen = Str\width($rightText);

    [$statusLeft, $statusRight] = Layout\horizontal($statusBar, [
        Layout\fill(),
        Layout\fixed($rightLen),
    ]);

    $totalCards = 0;
    foreach ($state->columns as $col) {
        $totalCards += Iter\count($col);
    }

    Widget\Paragraph::new([Widget\Line::new([
        Widget\Span::styled(" {$totalCards} cards", Ansi\foreground(Color\bright_black())),
    ])])->render($statusLeft, $buffer);

    Widget\Paragraph::new([Widget\Line::new([
        Widget\Span::styled($rightText, Ansi\foreground(Color\bright_black())),
    ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
}

$app = Terminal\Application::create(
    new KanbanState(),
    title: 'Kanban Board',
    tickInterval: DateTime\Duration::milliseconds(4),
);

$app->on(Event\Key::class, static function (Event\Key $event, KanbanState $state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
        return;
    }

    if ($state->input_mode) {
        namespace\handle_input_key($event, $state);
        return;
    }

    namespace\handle_normal_key($event, $state);
});

$app->on(Event\Mouse::class, static function (Event\Mouse $event, KanbanState $state): void {
    if ($event->kind !== Event\MouseKind::ScrollUp && $event->kind !== Event\MouseKind::ScrollDown) {
        return;
    }

    $col = null;
    foreach ($state->col_rects as $idx => $rect) {
        if (
            !(
                $event->column >= $rect->x
                && $event->column < $rect->right()
                && $event->row >= $rect->y
                && $event->row < $rect->bottom()
            )
        ) {
            continue;
        }

        $col = $idx;
        break;
    }

    if ($col === null) {
        return;
    }

    $cardCount = Iter\count($state->columns[$col]);
    $visibleSlots = $state->col_visible_slots[$col] ?? 1;
    $maxScroll = Math\maxva(0, $cardCount - $visibleSlots);
    $oldScroll = $state->col_scroll[$col];

    if ($event->kind === Event\MouseKind::ScrollUp) {
        if ($oldScroll <= 0) {
            return;
        }

        $state->col_scroll[$col] = $oldScroll - 1;
        if ($state->selected[$col] >= ($state->col_scroll[$col] + $visibleSlots)) {
            $state->selected[$col] = $state->col_scroll[$col] + $visibleSlots - 1;
        }
    } else {
        if ($oldScroll >= $maxScroll) {
            return;
        }

        $state->col_scroll[$col] = $oldScroll + 1;
        if ($state->selected[$col] < $state->col_scroll[$col]) {
            $state->selected[$col] = $state->col_scroll[$col];
        }
    }

    $state->active_col = $col;
});

$app->run(static function (Terminal\Frame $frame, KanbanState $state): void {
    $buffer = $frame->buffer();

    [$main, $statusBar] = Layout\vertical($frame, [
        Layout\fill(),
        Layout\fixed(1),
    ]);

    $colRects = Layout\horizontal($main, [
        Layout\fill(),
        Layout\fill(),
        Layout\fill(),
    ]);

    $state->col_rects = $colRects;

    foreach ($state->columns as $colIdx => $cards) {
        namespace\render_column($colRects[$colIdx], $cards, $colIdx, $state, $buffer);
    }

    namespace\render_status_bar($statusBar, $state, $buffer);
});
