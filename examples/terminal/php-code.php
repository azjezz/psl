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

use const PHP_INT_MAX;

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
    public float $displayedFps = 60.0;
    public int $frameCount = 0;
    public null|DateTime\Timestamp $lastFpsUpdate = null;
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
                Role::User => [Widget\Span::styled('You: ', Ansi\foreground(Color\bright_cyan()), Style\bold())],
                Role::Assistant => [Widget\Span::styled(
                    'Assistant: ',
                    Ansi\foreground(Color\bright_magenta()),
                    Style\bold(),
                )],
                Role::Tool => [Widget\Span::styled(
                    "  [{$message->tool}] ",
                    Ansi\foreground(Color\bright_yellow()),
                    Style\dim(),
                )],
                Role::System => [],
            } : [];

        $textSpan = match ($message->role) {
            Role::Tool => Widget\Span::styled($textLine, Ansi\foreground(Color\bright_black())),
            Role::System => Widget\Span::styled($textLine, Ansi\foreground(Color\bright_black()), Style\italic()),
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
        return namespace\COMMANDS;
    }

    $prefix = Str\lowercase($input);

    return Vec\filter::<array>(namespace\COMMANDS, static fn(array $cmd): bool => Str\starts_with($cmd[0], $prefix));
}

function handle_autocomplete(Event\Key $event, PhpCodeState $state): bool
{
    $filtered = namespace\filter_commands($state->input);

    if ($event->is('up') && $filtered !== []) {
        $state->ui->ac_selected = ($state->ui->ac_selected - 1 + Iter\count::<array>($filtered)) % Iter\count::<array>($filtered);
        return true;
    }

    if ($event->is('down') && $filtered !== []) {
        $state->ui->ac_selected = ($state->ui->ac_selected + 1) % Iter\count::<array>($filtered);
        return true;
    }

    if (($event->is('tab') || $event->is('enter')) && $filtered !== []) {
        /** @var non-negative-int $selected */
        $selected = Math\minva::<int>($state->ui->ac_selected, Iter\count::<array>($filtered) - 1);
        $state->input = $filtered[$selected][0];
        $state->ui->ac_selected = 0;
        if ($event->is('tab')) {
            return true;
        }
    }

    if ($event->is('escape')) {
        $state->input = '';
        $state->ui->ac_selected = 0;
        return true;
    }

    return false;
}

function handle_submit(string $prompt, PhpCodeState $state, Terminal\Application<PhpCodeState> $app): void
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
        foreach (namespace\COMMANDS as [$name, $desc]) {
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
        Async\run::<void>(static function () use ($state, $app): void {
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

    $state->status = 'Thinking...';
    $state->busy = true;
    $app->emit(Screen\progress(Screen\ProgressState::Indeterminate));
    Async\run::<void>(static function () use ($prompt, $state, $app): void {
        $responses = namespace\mock_llm_response($prompt);

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
        $lastSpace = Str\search_last($state->input, ' ');
        $state->input = $lastSpace !== null ? Str\slice($state->input, 0, $lastSpace + 1) : '';
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
    $now = DateTime\Timestamp::monotonic();
    $state->frameCount++;
    if ($state->lastFpsUpdate === null) {
        $state->lastFpsUpdate = $now;
    } else {
        $elapsed = $now->since($state->lastFpsUpdate)->getTotalSeconds();
        if ($elapsed >= 1.0) {
            $state->displayedFps = $state->frameCount / $elapsed;
            $state->frameCount = 0;
            $state->lastFpsUpdate = $now;
        }
    }

    $fps = $state->displayedFps;

    [$main, $statusBar] = Layout\vertical($frame, [
        Layout\fill(),
        Layout\fixed(1),
    ]);

    [$sidebar, $chatColumn] = Layout\horizontal($main, [
        Layout\fixed(28),
        Layout\fill(),
    ]);

    $inputLines = Str\contains($state->input, "\n") ? Iter\count::<string>(Str\split($state->input, "\n")) : 1;
    $inputHeight = Math\minva::<int>($inputLines + 2, 10);

    [$chatArea, $inputArea] = Layout\vertical($chatColumn, [
        Layout\fill(),
        Layout\max(10, Layout\fixed($inputHeight)),
    ]);

    namespace\render_sidebar($sidebar, $state, $buffer);
    namespace\render_chat($chatArea, $state, $spinners, $buffer);
    namespace\render_input($inputArea, $chatArea, $state, $buffer);
    namespace\render_php_code_status_bar($statusBar, $state, $fps, $buffer);
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
        ->titleStyle(Ansi\foreground(Color\bright_cyan()), Style\bold())
        ->border(Widget\Border::rounded())
        ->render(
            $area,
            Widget\Menu::new($sidebarItems)->highlight($state->ui->sidebar_active)->highlightStyle(
                Ansi\foreground(Color\bright_white()),
                Style\double_underline(),
            ),
            $buffer,
        );
}

/**
 * @param list<string> $spinners
 */
function render_chat(Terminal\Rect $chatArea, PhpCodeState $state, array $spinners, Terminal\Buffer $buffer): void
{
    $lines = Vec\flat_map::<Message, Widget\Line>($state->messages, static fn(Message $msg): array => [
        ...namespace\format_message($msg),
        Widget\Line::empty(),
    ]);

    if ($state->busy) {
        /** @var non-negative-int $idx */
        $idx = $state->ui->spinner_frame % 10;
        $spinner = $spinners[$idx];
        $lines[] = Widget\Line::new([
            Widget\Span::styled("  {$spinner} ", Ansi\foreground(Color\bright_yellow()), Style\bold()),
            Widget\Span::styled($state->status, Ansi\foreground(Color\bright_black()), Style\italic()),
        ]);
    }

    $chatBlock = Widget\Block::new()
        ->title(' Chat ')
        ->border(Widget\Border::rounded())
        ->padding(right: 2, left: 1);

    $chatInner = $chatBlock->innerArea($chatArea);
    $totalLines = Iter\count::<Widget\Line>($lines);
    $visibleLines = Math\maxva::<int>(1, $chatInner->height);

    $state->scroll_offset = Math\maxva::<int>(0, Math\minva::<int>($state->scroll_offset, Math\maxva::<int>(0, $totalLines - 1)));

    $chatBlock->render(
        $chatArea,
        Widget\Paragraph::new($lines)->scroll($state->scroll_offset)->wrap(Widget\Wrap::Word),
        $buffer,
    );

    $scrollbarRect = new Terminal\Rect($chatArea->right() - 2, $chatInner->y, 1, $chatInner->height);

    Widget\Scrollbar::new()
        ->contentLength($totalLines)
        ->viewportLength($visibleLines)
        ->position($state->scroll_offset)
        ->thumbStyle(Ansi\foreground(Color\bright_white()))
        ->trackStyle(Ansi\foreground(Color\ansi256(238)))
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
            ? [Widget\Span::styled('> ', Ansi\foreground(Color\bright_cyan()), Style\bold())]
            : [Widget\Span::styled('  ', Ansi\foreground(Color\bright_cyan()))];

        $isLastLine = $i === (Iter\count::<string>($inputLines) - 1);
        $suffix = $isLastLine ? [Widget\Span::styled($cursor, Ansi\foreground(Color\bright_cyan()))] : [];

        $inputWidgetLines[] = Widget\Line::new([...$prefix, Widget\Span::raw($line), ...$suffix]);
    }

    $inputVisibleHeight = $inputArea->height - 2;
    $inputScroll = Math\maxva::<int>(0, Iter\count::<Widget\Line>($inputWidgetLines) - $inputVisibleHeight);

    Widget\Block::new()->border(Widget\Border::rounded(Ansi\foreground($borderColor)))->render(
        $inputArea,
        Widget\Paragraph::new($inputWidgetLines)->scroll($inputScroll)->wrap(Widget\Wrap::Word),
        $buffer,
    );

    if (!Str\starts_with($state->input, '/') || $state->busy) {
        return;
    }

    $filtered = namespace\filter_commands($state->input);
    if ($filtered === []) {
        return;
    }

    $popupWidth = 40;
    $popupHeight = Iter\count::<array>($filtered) + 2;
    $popupX = $inputArea->x + 3;
    $popupY = $inputArea->y - $popupHeight;

    if ($popupY < $chatArea->y) {
        return;
    }

    $popupRect = new Terminal\Rect($popupX, $popupY, $popupWidth, $popupHeight);

    $items = Vec\map::<int, array, Widget\MenuItem>($filtered, static fn(array $cmd): Widget\MenuItem => Widget\MenuItem::styled([
        Widget\Span::styled($cmd[0], Ansi\foreground(Color\bright_cyan()), Style\bold()),
        Widget\Span::styled(' ' . $cmd[1], Ansi\foreground(Color\bright_black())),
    ]));

    $selected = Math\minva::<int>($state->ui->ac_selected, Iter\count::<array>($filtered) - 1);

    Widget\Block::new()->border(Widget\Border::rounded(Ansi\foreground(Color\bright_black())))->render(
        $popupRect,
        Widget\Menu::new($items)->highlight($selected)->highlightStyle(
            Ansi\foreground(Color\bright_white()),
            Ansi\background(Color\ansi256(236)),
            Style\bold(),
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
        Widget\Span::styled(' ' . $state->status, Ansi\foreground(Color\bright_black())),
    ])])->render($statusLeft, $buffer);

    Widget\Paragraph::new([Widget\Line::new([
        Widget\Span::styled($fpsStr . ' fps', Ansi\foreground(Color\bright_green())),
        Widget\Span::styled(
            " · {$state->ui->token_count} tokens · \${$costStr} ",
            Ansi\foreground(Color\bright_black()),
        ),
    ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
}

Async\main(static function (): int {
    $app = Terminal\Application::create::<PhpCodeState>(new PhpCodeState(), title: 'php-code');

    $spinners = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    $app->interval(DateTime\Duration::milliseconds(80), static function (PhpCodeState $state): void {
        if ($state->busy) {
            $state->ui->spinner_frame++;
        }
    });

    $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $event, PhpCodeState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
            return;
        }

        if ($event->is('ctrl+up') || $event->is('page_up')) {
            $state->scroll_offset = Math\maxva::<int>($state->scroll_offset - 3, 0);
            return;
        }

        if ($event->is('ctrl+down') || $event->is('page_down')) {
            $state->scroll_offset += 3;
            return;
        }

        if (Str\starts_with($state->input, '/') && !$state->busy) {
            if (namespace\handle_autocomplete($event, $state)) {
                return;
            }
        }

        if ($event->is('shift+enter')) {
            $state->input .= "\n";
            return;
        }

        if ($event->is('tab')) {
            $state->ui->sidebar_active = ($state->ui->sidebar_active + 1) % 4;
            return;
        }

        if ($event->is('shift+tab')) {
            $state->ui->sidebar_active = ($state->ui->sidebar_active - 1 + 4) % 4;
            return;
        }

        if ($event->is('enter') && Str\trim($state->input) !== '') {
            $prompt = Str\trim($state->input);
            if ($state->busy) {
                $state->input = $prompt;
                return;
            }

            namespace\handle_submit($prompt, $state, $app);
            return;
        }

        namespace\handle_text_editing($event, $state);
    });

    $app->on::<Event\Paste>(Event\Paste::class, static function (Event\Paste $event, PhpCodeState $state): void {
        $state->input .= $event->text;
        $state->ui->ac_selected = 0;
    });

    $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, PhpCodeState $state): void {
        if ($event->kind === Event\MouseKind::ScrollUp) {
            $state->scroll_offset = Math\maxva::<int>($state->scroll_offset - 3, 0);
        }

        if ($event->kind === Event\MouseKind::ScrollDown) {
            $state->scroll_offset += 3;
        }
    });

    return $app->run(static function (Terminal\Frame $frame, PhpCodeState $state) use ($spinners): void {
        namespace\render_frame($frame, $state, $spinners);
    });
});
