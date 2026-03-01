<?php

declare(strict_types=1);

namespace Psl\Example\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Screen;
use Psl\Ansi\Style;
use Psl\Async;
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

/**
 * Available slash commands: [name, description].
 *
 * @var list<array{string, string}>
 */
const COMMANDS = [
    ['/help',  'Show available commands'],
    ['/load',  'Simulate a 5-second loading task'],
    ['/clear', 'Clear the chat history'],
    ['/exit',  'Quit the application'],
];

enum Role: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
    case Tool = 'tool';
}

final readonly class Message
{
    public function __construct(
        public Role $role,
        public string $text,
        public null|string $tool = null,
    ) {}
}

/**
 * UI-specific display state grouped together.
 */
final class UiMetrics
{
    public int $token_count = 0;
    public float $cost = 0.0;
    public int $spinner_frame = 0;
    public int $ac_selected = 0;
    public int $sidebar_active = 0;
}

final class PhpCodeState
{
    /** @var list<Message> */
    public array $messages;
    public string $input = '';
    public string $status = 'Ready';
    public bool $busy = false;
    public int $scroll_offset = 0;
    public UiMetrics $ui;

    public function __construct()
    {
        $this->ui = new UiMetrics();
        $this->messages = [
            new Message(Role::System, 'Welcome to php-code! Type a message and press Enter.'),
            new Message(Role::System, 'Type / to see available commands. Press Ctrl+C to exit.'),
        ];
    }
}

/**
 * Simulate an LLM response with realistic delay.
 *
 * @return list<Message>
 */
function mock_llm_response(string $prompt): array
{
    Async\sleep(DateTime\Duration::milliseconds(800));

    if (Str\contains_ci($prompt, 'file') || Str\contains_ci($prompt, 'read') || Str\contains_ci($prompt, 'code')) {
        return [
            new Message(Role::Tool, 'Read src/Psl/Terminal/Application.php (142 lines)', tool: 'Read'),
            new Message(
                Role::Assistant,
                'I\'ve read the file. The Application class handles the event loop and frame rendering. It uses an async scheduler to poll for terminal events and triggers a redraw on each tick.',
            ),
        ];
    }

    if (Str\contains_ci($prompt, 'help') || Str\contains_ci($prompt, 'what')) {
        return [
            new Message(
                Role::Assistant,
                'I can help you with reading and editing files, running shell commands, searching the codebase, and answering questions about your code. What would you like to do?',
            ),
        ];
    }

    return [
        new Message(
            Role::Assistant,
            'I\'ll look into that. Let me search the codebase for relevant files and get back to you with a detailed answer.',
        ),
    ];
}

/**
 * Format a message for display in the chat log.
 *
 * @return list<Widget\Line>
 */
function format_message(Message $message): array
{
    $textLines = Str\split($message->text, "\n");
    $result = [];

    foreach ($textLines as $i => $textLine) {
        $prefix = $i === 0
            ? match ($message->role) {
                Role::User => [Widget\Span::styled('You: ', foreground: Color\bright_cyan(), style: Style\bold())],
                Role::Assistant => [Widget\Span::styled(
                    'Assistant: ',
                    foreground: Color\bright_magenta(),
                    style: Style\bold(),
                )],
                Role::Tool => [Widget\Span::styled(
                    "  [{$message->tool}] ",
                    foreground: Color\bright_yellow(),
                    style: Style\dim(),
                )],
                Role::System => [],
            } : [];

        $textSpan = match ($message->role) {
            Role::Tool => Widget\Span::styled($textLine, foreground: Color\bright_black()),
            Role::System => Widget\Span::styled($textLine, foreground: Color\bright_black(), style: Style\italic()),
            default => Widget\Span::raw($textLine),
        };

        $result[] = Widget\Line::new([...$prefix, $textSpan]);
    }

    return $result;
}

/**
 * Filter commands matching the current input prefix.
 *
 * @return list<array{string, string}>
 */
function filter_commands(string $input): array
{
    if ($input === '/') {
        return COMMANDS;
    }

    $prefix = Str\lowercase($input);

    return Vec\filter(COMMANDS, static fn(array $cmd): bool => Str\starts_with($cmd[0], $prefix));
}

function handle_autocomplete(Event\Key $event, PhpCodeState $state): bool
{
    $filtered = filter_commands($state->input);

    if ($event->is('up') && $filtered !== []) {
        $state->ui->ac_selected = ($state->ui->ac_selected - 1 + Iter\count($filtered)) % Iter\count($filtered);
        return true;
    }

    if ($event->is('down') && $filtered !== []) {
        $state->ui->ac_selected = ($state->ui->ac_selected + 1) % Iter\count($filtered);
        return true;
    }

    // Tab or Enter accepts the selected autocomplete item
    if (($event->is('tab') || $event->is('enter')) && $filtered !== []) {
        /** @var non-negative-int $selected */
        $selected = Math\minva($state->ui->ac_selected, Iter\count($filtered) - 1);
        $state->input = $filtered[$selected][0];
        $state->ui->ac_selected = 0;
        // If tab was pressed, consume the event; if enter, fall through to submit
        if ($event->is('tab')) {
            return true;
        }
    }

    // Escape dismisses the autocomplete
    if ($event->is('escape')) {
        $state->input = '';
        $state->ui->ac_selected = 0;
        return true;
    }

    return false;
}

function handle_submit(string $prompt, PhpCodeState $state, Terminal\Application $app): void
{
    $state->input = '';
    $state->ui->ac_selected = 0;
    $state->scroll_offset = PHP_INT_MAX;

    if ($prompt === '/exit') {
        $state->messages[] = new Message(Role::System, 'Goodbye!');
        $app->stop();
        return;
    }

    if ($prompt === '/clear') {
        $state->messages = [new Message(Role::System, 'Chat cleared.')];
        return;
    }

    if ($prompt === '/help') {
        $state->messages[] = new Message(Role::User, $prompt);
        $state->messages[] = new Message(Role::System, 'Available commands:');
        foreach (COMMANDS as [$name, $desc]) {
            $state->messages[] = new Message(Role::System, "  {$name} — {$desc}");
        }

        $state->scroll_offset = PHP_INT_MAX;
        return;
    }

    $state->messages[] = new Message(Role::User, $prompt);

    if ($prompt === '/load') {
        $state->status = 'Loading... 0%';
        $state->busy = true;
        $app->emit(Screen\progress(Screen\ProgressState::Normal, 0));
        Async\run(static function () use ($state, $app): void {
            for ($i = 1; $i <= 50; $i++) {
                Async\sleep(DateTime\Duration::milliseconds(100));
                /** @var int<0, 100> $pct */
                $pct = $i * 2;
                $state->status = "Loading... {$pct}%";
                $app->emit(Screen\progress(Screen\ProgressState::Normal, $pct));
            }

            $state->messages[] = new Message(Role::System, 'Loading complete! Finished after 5 seconds.');
            $state->busy = false;
            $state->status = 'Ready';
            $state->scroll_offset = PHP_INT_MAX;
            $app->emit(Screen\progress_clear());
        });
        return;
    }

    // Regular message — run LLM in background fiber
    $state->status = 'Thinking...';
    $state->busy = true;
    $app->emit(Screen\progress(Screen\ProgressState::Indeterminate));
    Async\run(static function () use ($prompt, $state, $app): void {
        $responses = mock_llm_response($prompt);

        foreach ($responses as $response) {
            $state->messages[] = $response;
        }

        $tokens = Str\length($prompt) * 2;
        $state->ui->token_count += $tokens;
        $state->ui->cost += $tokens * 0.000_015;

        $state->busy = false;
        $state->status = 'Ready';
        $state->scroll_offset = PHP_INT_MAX;
        $app->emit(Screen\progress_clear());
    });
}

function handle_text_editing(Event\Key $event, PhpCodeState $state): void
{
    if ($event->is('backspace')) {
        if ($state->input !== '') {
            /** @var non-negative-int $newLen */
            $newLen = Str\length($state->input) - 1;
            $state->input = Str\slice($state->input, 0, $newLen);
            $state->ui->ac_selected = 0;
        }

        return;
    }

    if ($event->is('ctrl+u')) {
        $state->input = '';
        $state->ui->ac_selected = 0;
        return;
    }

    if ($event->is('ctrl+w')) {
        $state->input = Str\trim_right($state->input);
        $last_space = Str\search_last($state->input, ' ');
        $state->input = $last_space !== null ? Str\slice($state->input, 0, $last_space + 1) : '';
        $state->ui->ac_selected = 0;
        return;
    }

    if ($event->char !== null) {
        $state->input .= $event->char;
        $state->ui->ac_selected = 0;
    }
}

/**
 * @param list<string> $spinners
 */
function render_frame(Terminal\Frame $frame, PhpCodeState $state, array $spinners): void
{
    $buffer = $frame->buffer();
    $fps = $frame->fps();

    [$main, $statusBar] = Layout\vertical($frame, [
        Layout\fill(),
        Layout\fixed(1),
    ]);

    [$sidebar, $chatColumn] = Layout\horizontal($main, [
        Layout\fixed(28),
        Layout\fill(),
    ]);

    // Input height grows with newlines
    $inputLines = Str\contains($state->input, "\n") ? Iter\count(Str\split($state->input, "\n")) : 1;
    $inputHeight = Math\minva($inputLines + 2, 10);

    [$chatArea, $inputArea] = Layout\vertical($chatColumn, [
        Layout\fill(),
        Layout\max(10, Layout\fixed($inputHeight)),
    ]);

    render_sidebar($sidebar, $state, $buffer);
    render_chat($chatArea, $state, $spinners, $buffer);
    render_input($inputArea, $chatArea, $state, $buffer);
    render_php_code_status_bar($statusBar, $state, $fps, $buffer);
}

function render_sidebar(Terminal\Rect $area, PhpCodeState $state, Terminal\Buffer $buffer): void
{
    $sidebarItems = [
        Widget\MenuItem::raw('  New conversation'),
        Widget\MenuItem::raw('  Fix auth bug (#42)'),
        Widget\MenuItem::raw('  Refactor DB layer'),
        Widget\MenuItem::raw('  Add terminal component'),
    ];

    Widget\Block::new()
        ->title(' php-code ')
        ->titleStyle(foreground: Color\bright_cyan(), style: Style\bold())
        ->border(Widget\Border::rounded())
        ->render(
            $area,
            Widget\Menu::new($sidebarItems)->highlight($state->ui->sidebar_active)->highlightStyle(
                foreground: Color\bright_white(),
                style: Style\double_underline(),
            ),
            $buffer,
        );
}

/**
 * @param list<string> $spinners
 */
function render_chat(Terminal\Rect $chatArea, PhpCodeState $state, array $spinners, Terminal\Buffer $buffer): void
{
    $lines = Vec\flat_map($state->messages, static fn(Message $msg): array => [
        ...format_message($msg),
        Widget\Line::empty(),
    ]);

    if ($state->busy) {
        /** @var non-negative-int $idx */
        $idx = $state->ui->spinner_frame % 10;
        $spinner = $spinners[$idx];
        $lines[] = Widget\Line::new([
            Widget\Span::styled("  {$spinner} ", foreground: Color\bright_yellow(), style: Style\bold()),
            Widget\Span::styled($state->status, foreground: Color\bright_black(), style: Style\italic()),
        ]);
    }

    $chatBlock = Widget\Block::new()
        ->title(' Chat ')
        ->border(Widget\Border::rounded())
        ->padding(right: 2, left: 1);

    $chatInner = $chatBlock->innerArea($chatArea);
    $totalLines = Iter\count($lines);
    $visibleLines = Math\maxva(1, $chatInner->height);

    // Clamp scroll_offset to valid range (don't go negative, don't exceed content)
    $state->scroll_offset = Math\maxva(0, Math\minva($state->scroll_offset, Math\maxva(0, $totalLines - 1)));

    $chatBlock->render(
        $chatArea,
        Widget\Paragraph::new($lines)->scroll($state->scroll_offset)->wrap(Widget\Wrap::Word),
        $buffer,
    );

    // Scrollbar inside the block, in the right padding gap
    $scrollbarRect = new Terminal\Rect($chatArea->right() - 2, $chatInner->y, 1, $chatInner->height);

    Widget\Scrollbar::new()
        ->contentLength($totalLines)
        ->viewportLength($visibleLines)
        ->position($state->scroll_offset)
        ->thumbStyle(foreground: Color\bright_white())
        ->trackStyle(foreground: Color\ansi256(238))
        ->render($scrollbarRect, $buffer);
}

function render_input(
    Terminal\Rect $inputArea,
    Terminal\Rect $chatArea,
    PhpCodeState $state,
    Terminal\Buffer $buffer,
): void {
    $cursor = '█';
    $borderColor = $state->busy ? Color\bright_black() : Color\bright_cyan();

    $inputLines = Str\split($state->input, "\n");
    /** @var list<Widget\Line> $inputWidgetLines */
    $inputWidgetLines = [];
    foreach ($inputLines as $i => $line) {
        $prefix = $i === 0
            ? [Widget\Span::styled('> ', foreground: Color\bright_cyan(), style: Style\bold())]
            : [Widget\Span::styled('  ', foreground: Color\bright_cyan())];

        $isLastLine = $i === (Iter\count($inputLines) - 1);
        $suffix = $isLastLine ? [Widget\Span::styled($cursor, foreground: Color\bright_cyan())] : [];

        $inputWidgetLines[] = Widget\Line::new([...$prefix, Widget\Span::raw($line), ...$suffix]);
    }

    $inputVisibleHeight = $inputArea->height - 2;
    $inputScroll = Math\maxva(0, Iter\count($inputWidgetLines) - $inputVisibleHeight);

    Widget\Block::new()->border(Widget\Border::rounded(color: $borderColor))->render(
        $inputArea,
        Widget\Paragraph::new($inputWidgetLines)->scroll($inputScroll)->wrap(Widget\Wrap::Word),
        $buffer,
    );

    // Autocomplete popup
    if (!Str\starts_with($state->input, '/') || $state->busy) {
        return;
    }

    $filtered = filter_commands($state->input);
    if ($filtered === []) {
        return;
    }

    $popupWidth = 40;
    $popupHeight = Iter\count($filtered) + 2;
    $popupX = $inputArea->x + 3;
    $popupY = $inputArea->y - $popupHeight;

    if ($popupY < $chatArea->y) {
        return;
    }

    $popupRect = new Terminal\Rect($popupX, $popupY, $popupWidth, $popupHeight);

    $items = Vec\map($filtered, static fn(array $cmd): Widget\MenuItem => Widget\MenuItem::styled([
        Widget\Span::styled($cmd[0], foreground: Color\bright_cyan(), style: Style\bold()),
        Widget\Span::styled(' ' . $cmd[1], foreground: Color\bright_black()),
    ]));

    $selected = Math\minva($state->ui->ac_selected, Iter\count($filtered) - 1);

    Widget\Block::new()->border(Widget\Border::rounded(color: Color\bright_black()))->render(
        $popupRect,
        Widget\Menu::new($items)->highlight($selected)->highlightStyle(
            foreground: Color\bright_white(),
            background: Color\ansi256(236),
            style: Style\bold(),
        ),
        $buffer,
    );
}

function render_php_code_status_bar(
    Terminal\Rect $statusBar,
    PhpCodeState $state,
    float $fps,
    Terminal\Buffer $buffer,
): void {
    $fpsStr = Str\format('%.0f', $fps);
    $costStr = Str\format('%.4f', $state->ui->cost);
    $rightText = "{$fpsStr} fps · {$state->ui->token_count} tokens · \${$costStr} ";
    $rightLen = Str\width($rightText);

    [$statusLeft, $statusRight] = Layout\horizontal($statusBar, [
        Layout\fill(),
        Layout\fixed($rightLen),
    ]);

    Widget\Paragraph::new([Widget\Line::new([
        Widget\Span::styled(' ' . $state->status, foreground: Color\bright_black()),
    ])])->render($statusLeft, $buffer);

    Widget\Paragraph::new([Widget\Line::new([
        Widget\Span::styled($fpsStr . ' fps', foreground: Color\bright_green()),
        Widget\Span::styled(" · {$state->ui->token_count} tokens · \${$costStr} ", foreground: Color\bright_black()),
    ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
}

Async\main(static function (): int {
    $app = Terminal\Application::create(new PhpCodeState(), title: 'php-code', fps: 120);

    $spinners = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    // Spinner tick
    $app->interval(DateTime\Duration::milliseconds(80), static function (PhpCodeState $state): void {
        if ($state->busy) {
            $state->ui->spinner_frame++;
        }
    });

    // Keyboard events
    $app->on(Event\Key::class, static function (Event\Key $event, PhpCodeState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
            return;
        }

        // Scrolling always works
        if ($event->is('ctrl+up') || $event->is('page_up')) {
            $state->scroll_offset = Math\maxva($state->scroll_offset - 3, 0);
            return;
        }

        if ($event->is('ctrl+down') || $event->is('page_down')) {
            $state->scroll_offset += 3;
            return;
        }

        // Autocomplete navigation
        if (Str\starts_with($state->input, '/') && !$state->busy) {
            if (handle_autocomplete($event, $state)) {
                return;
            }
        }

        if ($event->is('shift+enter')) {
            $state->input .= "\n";
            return;
        }

        // Tab cycles sidebar highlight (when not in autocomplete)
        if ($event->is('tab')) {
            $state->ui->sidebar_active = ($state->ui->sidebar_active + 1) % 4;
            return;
        }

        if ($event->is('shift+tab')) {
            $state->ui->sidebar_active = ($state->ui->sidebar_active - 1 + 4) % 4;
            return;
        }

        // Submit on Enter
        if ($event->is('enter') && Str\trim($state->input) !== '') {
            $prompt = Str\trim($state->input);
            if ($state->busy) {
                $state->input = $prompt;
                return;
            }

            handle_submit($prompt, $state, $app);
            return;
        }

        // Text editing
        handle_text_editing($event, $state);
    });

    // Paste events
    $app->on(Event\Paste::class, static function (Event\Paste $event, PhpCodeState $state): void {
        $state->input .= $event->text;
        $state->ui->ac_selected = 0;
    });

    // Mouse events
    $app->on(Event\Mouse::class, static function (Event\Mouse $event, PhpCodeState $state): void {
        if ($event->kind === Event\MouseKind::ScrollUp) {
            $state->scroll_offset = Math\maxva($state->scroll_offset - 3, 0);
        }

        if ($event->kind === Event\MouseKind::ScrollDown) {
            $state->scroll_offset += 3;
        }
    });

    return $app->run(static function (Terminal\Frame $frame, PhpCodeState $state) use ($spinners): void {
        render_frame($frame, $state, $spinners);
    });
});
