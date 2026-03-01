# Terminal

The `Terminal` component provides a full-featured TUI (Terminal User Interface) framework built on top of the `Ansi` and `Async` components.

It handles the event loop, raw mode, input parsing, diff-based rendering, and provides a library of composable widgets for building interactive terminal applications.

## Quick Start

```php
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;

Async\main(static function (): int {
    $app = Terminal\Application::create(new MyState(), title: 'My App');

    $app->on(Event\Key::class, static function (Event\Key $event, MyState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }
    });

    return $app->run(static function (Terminal\Frame $frame, MyState $state): void {
        $buffer = $frame->buffer();

        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Hello, World!')]),
        ])->render($frame->rect(), $buffer);
    });
});
```

## Architecture

The framework follows an immediate-mode rendering model:

1. **Application** manages the event loop, terminal setup/teardown, and render scheduling
2. **Events** are parsed from raw stdin bytes and dispatched to registered handlers
3. **Frame** is provided to the render callback each tick with a fresh **Buffer**
4. **Widgets** render themselves into a rectangular **Rect** area of the **Buffer**
5. **Buffer** diffs against the previous frame and writes only changed cells

## Application

### `Application::create()` — Local Terminal

For local terminal applications using STDIN/STDOUT:

```php
$app = Terminal\Application::create(
    state: new MyState(),
    title: 'My App',
    tickInterval: DateTime\Duration::milliseconds(16),  // ~60 ticks/s (default: 15ms)
    scrollSmoothing: true,   // filter trackpad scroll micro-reversals (default: true)
    mouseMotion: false,      // track mouse movement, not just clicks (default: false)
);
```

This automatically handles raw mode, terminal size detection, SIGWINCH/SIGINT signal handling, and in-band resize notifications (mode 2048).

### `Application::custom()` — Custom I/O

For remote scenarios (e.g. SSH servers) or testing, where you provide your own I/O handles:

```php
$app = Terminal\Application::custom(
    state: new MyState(),
    input: $sshInputStream,
    output: $sshOutputStream,
    width: 80,
    height: 24,
    title: 'Remote App',
);
```

In this mode:
- Raw mode is **not** managed — the caller is responsible for it
- Signal handlers (SIGWINCH, SIGINT) are **not** registered
- Ctrl+C is handled via the input stream parser (`0x03` byte)
- Use `$app->dispatch(new Event\Resize($cols, $rows))` to inject resize events

### Event Handling

Register handlers for specific event types. Multiple handlers per event type are supported:

```php
$app->on(Event\Key::class, function (Event\Key $event, MyState $state) use ($app): void {
    // handle keyboard input
});

$app->on(Event\Mouse::class, function (Event\Mouse $event, MyState $state): void {
    // handle mouse clicks, scrolling, dragging
});

$app->on(Event\Paste::class, function (Event\Paste $event, MyState $state): void {
    // handle bracketed paste
});

$app->on(Event\Resize::class, function (Event\Resize $event, MyState $state): void {
    // handle terminal resize
});

$app->on(Event\Focus::class, function (Event\Focus $event, MyState $state): void {
    // handle window focus/blur
});
```

### Periodic Callbacks

Register interval-based callbacks for tasks like polling or animation:

```php
use Psl\DateTime\Duration;

$app->interval(Duration::milliseconds(100), static function (MyState $state): void {
    // runs every 100ms, e.g. game tick
});
```

### Dispatching Events

Inject events from external sources (e.g. SSH window-change messages):

```php
// resize the terminal
$app->dispatch(new Event\Resize($cols, $rows));

// simulate a key press
$app->dispatch(Event\Key::named('enter'));
```

Resize events automatically update the internal buffer and frame dimensions before reaching user handlers.

### Emitting Commands

Queue ANSI commands to be written after the next frame render:

```php
$app->emit(Ansi\Screen\progress(Ansi\Screen\ProgressState::Normal, 50));
$app->emit(Ansi\bell());
```

### Run Loop

`run()` enters the event loop and blocks until `stop()` is called. It returns the exit code:

```php
$exitCode = $app->run(static function (Terminal\Frame $frame, MyState $state): void {
    $buffer = $frame->buffer();
    $rect = $frame->rect();

    // render your UI each frame
});
```

## Events

### Key

Keyboard input, including printable characters, special keys, and modifier combinations:

```php
$event->is('enter');       // named key check
$event->is('ctrl+c');      // modifier + key
$event->is('alt+x');       // alt combinations
$event->is('up');          // arrow keys
$event->is('page_down');   // navigation keys
$event->char;              // printable character or null
$event->name;              // key name string
```

### Mouse

Mouse events with position, button, and modifier state:

```php
$event->kind;               // MouseKind enum: Press, Release, Drag, ScrollUp, ScrollDown, Move
$event->button;             // MouseButton enum: Left, Middle, Right, None
$event->column;             // column position (0-based)
$event->row;                // row position (0-based)
$event->modifiers->shift(); // true if Shift held
$event->modifiers->alt();   // true if Alt held
$event->modifiers->ctrl();  // true if Ctrl held
```

### Paste

Bracketed paste content (text pasted from the system clipboard):

```php
$event->text;   // the pasted string
```

### Resize

Terminal window resize:

```php
$event->width;   // new width in columns
$event->height;  // new height in rows
```

### Focus

Window focus state changes:

```php
$event->focused;  // true = gained focus, false = lost focus
```

## Layout

The layout system splits rectangular areas into sub-regions using constraints:

```php
use Psl\Terminal\Layout;

// Vertical split: header (3 rows) + content (fill) + footer (1 row)
[$header, $content, $footer] = Layout\vertical($frame, [
    Layout\fixed(3),
    Layout\fill(),
    Layout\fixed(1),
]);

// Horizontal split: sidebar (20 cols) + main (fill)
[$sidebar, $main] = Layout\horizontal($content, [
    Layout\fixed(20),
    Layout\fill(),
]);

// Constraints can be wrapped with min/max
Layout\min(10, Layout\fill());     // fill, but at least 10
Layout\max(50, Layout\fill());     // fill, but at most 50
```

## Widgets

All widgets implement `WidgetInterface` with a single method: `render(Rect $area, Buffer $buffer): void`.

Widgets use a builder pattern for configuration — methods return `$this` for chaining.

All style methods accept variadic `ControlSequenceIntroducer` arguments. Use `Ansi\foreground()`, `Ansi\background()`, and `Style\*()` functions to build styles:

```php
->someStyle(Ansi\foreground(Color\red()), Style\bold())
->someStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\blue()))
```

### Paragraph

Multi-line text with wrapping, scrolling, and alignment:

```php
Widget\Paragraph::new([
    Widget\Line::new([
        Widget\Span::styled('Error: ', Ansi\foreground(Color\red()), Style\bold()),
        Widget\Span::raw('something went wrong'),
    ]),
    Widget\Line::new([Widget\Span::raw('Check the logs for details.')]),
])
    ->wrap(Widget\Wrap::Word)
    ->alignment(Widget\Alignment::Left)
    ->scroll(0)
    ->render($area, $buffer);
```

### Block

A container that draws a border and optional title around an inner widget:

```php
$block = Widget\Block::new()
    ->title(' Status ')
    ->titleStyle(Ansi\foreground(Color\bright_white()), Style\bold())
    ->border(Widget\Border::rounded(Ansi\foreground(Color\bright_cyan())))
    ->padding(left: 1, right: 1)
    ->margin(top: 1)
    ->background(Color\ansi256(235));

$block->render($area, $paragraph, $buffer);

// Calculate the inner area for layout purposes
$inner = $block->innerArea($area);
```

Border styles: `Border::rounded()`, `Border::plain()`, `Border::double()`, `Border::thick()`. Per-side control is available via the `Border` constructor.

### Table

Columnar data with headers, scrolling, and row highlighting:

```php
Widget\Table::new()
    ->headers(['Name', 'Status', 'CPU'])
    ->widths([15, 10, 8])
    ->rows([
        [Widget\Span::raw('nginx'), Widget\Span::styled('running', Ansi\foreground(Color\green())), Widget\Span::raw('2.1%')],
        [Widget\Span::raw('postgres'), Widget\Span::styled('running', Ansi\foreground(Color\green())), Widget\Span::raw('5.3%')],
    ])
    ->highlight(0)
    ->highlightStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\blue()))
    ->headerStyle(Style\bold(), Ansi\foreground(Color\bright_white()))
    ->scroll(0)
    ->render($area, $buffer);
```

### Menu

A selectable list of items with scrolling and highlight:

```php
Widget\Menu::new([
    Widget\MenuItem::raw('Open File'),
    Widget\MenuItem::raw('Save'),
    Widget\MenuItem::styled([
        Widget\Span::styled('Quit', Ansi\foreground(Color\red())),
    ]),
])
    ->highlight($selectedIndex)
    ->scroll($scrollOffset)
    ->highlightStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\blue()))
    ->render($area, $buffer);
```

### Tabs

A horizontal tab bar:

```php
Widget\Tabs::new()
    ->titles(['Overview', 'Details', 'Logs'])
    ->highlight($activeTab)
    ->activeStyle(Ansi\foreground(Color\bright_white()), Style\bold())
    ->inactiveStyle(Ansi\foreground(Color\bright_black()))
    ->render($area, $buffer);
```

### Gauge

A horizontal progress bar with label and percentage:

```php
Widget\Gauge::new()
    ->ratio(0.75)
    ->label('Progress')
    ->filledStyle(Ansi\foreground(Color\green()))
    ->emptyStyle(Ansi\foreground(Color\bright_black()))
    ->labelStyle(Style\bold())
    ->render($area, $buffer);
```

### Sparkline

A single-row data visualization using Unicode block characters:

```php
Widget\Sparkline::new($dataPoints)  // list<float>, each 0.0-1.0
    ->style(Ansi\foreground(Color\bright_cyan()))
    ->render($area, $buffer);
```

### BarChart

Vertical bar chart with labels:

```php
Widget\BarChart::new()
    ->data([['Mon', 0.8], ['Tue', 0.6], ['Wed', 0.9], ['Thu', 0.4]])
    ->barWidth(3)
    ->barGap(1)
    ->barStyle(Ansi\foreground(Color\bright_blue()))
    ->labelStyle(Ansi\foreground(Color\bright_white()))
    ->render($area, $buffer);
```

### Scrollbar

A vertical scrollbar indicating position within scrollable content:

```php
Widget\Scrollbar::new()
    ->contentLength($totalItems)
    ->viewportLength($visibleItems)
    ->position($scrollOffset)
    ->thumbStyle(Ansi\foreground(Color\bright_cyan()))
    ->trackStyle(Ansi\foreground(Color\ansi256(238)))
    ->render($scrollbarArea, $buffer);
```

### TextInput

A single-line text input with cursor and placeholder:

```php
Widget\TextInput::new()
    ->value($currentText)
    ->cursor($cursorPosition)
    ->placeholder('Type here...')
    ->style(Ansi\foreground(Color\bright_white()))
    ->cursorStyle(Ansi\foreground(Color\black()), Ansi\background(Color\bright_white()))
    ->placeholderStyle(Ansi\foreground(Color\bright_black()))
    ->render($area, $buffer);
```

## Text Primitives

### Span

A styled text fragment — the smallest text unit:

```php
Widget\Span::raw('plain text');
Widget\Span::styled('bold red', Ansi\foreground(Color\red()), Style\bold());
Widget\Span::styled('multi', Ansi\foreground(Color\cyan()), Style\bold(), Style\italic());
```

### Line

A horizontal sequence of spans:

```php
Widget\Line::new([
    Widget\Span::styled('[INFO] ', Ansi\foreground(Color\blue())),
    Widget\Span::raw('Server started on port 8080'),
]);
```

## Buffer

The `Buffer` is a 2D grid of `Cell` objects. Widgets write to it, and `flush()` performs diff-based rendering:

```php
$buffer = $frame->buffer();

// Direct cell manipulation
$buffer->set($x, $y, new Terminal\Cell('X', [Ansi\foreground(Color\red())]));
$buffer->setString($x, $y, 'Hello', [Ansi\foreground(Color\green()), Style\bold()]);

// Read cells (returns null for out-of-bounds)
$cell = $buffer->get($x, $y);
$grapheme = $cell?->grapheme;
```
