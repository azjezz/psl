# Terminal

The `Terminal` component provides a full-featured TUI (Terminal User Interface) framework built on top of the `Ansi` and `Async` components.

It handles the event loop, raw mode, input parsing, diff-based rendering, and provides a library of composable widgets for building interactive terminal applications.

## Quick Start

@example('terminal/terminal-quick-start.php')

## Architecture

The framework follows an immediate-mode rendering model:

1. **Application** manages the event loop, terminal setup/teardown, and render scheduling
2. **Events** are parsed from raw stdin bytes and dispatched to registered handlers
3. **Frame** is provided to the render callback each tick with a fresh **Buffer**
4. **Widgets** render themselves into a rectangular **Rect** area of the **Buffer**
5. **Buffer** diffs against the previous frame and writes only changed cells

## Application

### Local Terminal

For local terminal applications using STDIN/STDOUT:

@example('terminal/terminal-create.php')

This automatically handles raw mode, terminal size detection, SIGWINCH/SIGINT signal handling, and in-band resize notifications.

### Custom I/O

For remote scenarios (e.g. SSH servers) or testing, where you provide your own I/O handles:

@example('terminal/terminal-custom-io.php')

In this mode, raw mode is not managed by the application -- the caller is responsible for it. Signal handlers are not registered, and Ctrl+C is handled via the input stream parser. Use `$app->dispatch(new Event\Resize($cols, $rows))` to inject resize events.

## Event Handling

Register handlers for specific event types. Multiple handlers per event type are supported:

@example('terminal/terminal-events.php')

Periodic callbacks can be registered for tasks like polling or animation:

@example('terminal/terminal-interval.php')

## Layout

The layout system splits rectangular areas into sub-regions using constraints:

@example('terminal/terminal-layout.php')

## Widgets

All widgets implement `WidgetInterface` with a single method: `render(Rect $area, Buffer $buffer): void`. They use a builder pattern for configuration.

### Paragraph

Multi-line text with wrapping, scrolling, and alignment:

@example('terminal/terminal-paragraph.php')

### Block

A container that draws a border and optional title around an inner widget:

@example('terminal/terminal-block.php')

Border styles: `Border::rounded()`, `Border::plain()`, `Border::double()`, `Border::thick()`.

### Table

Columnar data with headers, scrolling, and row highlighting:

@example('terminal/terminal-table.php')

### Menu

A selectable list of items with scrolling and highlight:

@example('terminal/terminal-menu.php')

### Other Widgets

- **Tabs** -- horizontal tab bar with active/inactive styling
- **Gauge** -- horizontal progress bar with label and percentage
- **Sparkline** -- single-row data visualization using Unicode block characters
- **BarChart** -- vertical bar chart with labels
- **Scrollbar** -- vertical scrollbar for scrollable content
- **TextInput** -- single-line text input with cursor and placeholder

### Text Primitives

@example('terminal/terminal-text-primitives.php')

## Buffer

The `Buffer` is a 2D grid of `Cell` objects. Widgets write to it, and `flush()` performs diff-based rendering:

@example('terminal/terminal-buffer.php')

See `src/Psl/Terminal/` for the full API.
