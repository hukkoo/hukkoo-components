<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Forms\Concerns\RendersCalendarGrid;

defined('ABSPATH') || exit;

/**
 * A native <input type="date">'s calendar popup is OS-drawn — same
 * problem as <select>'s <option> list: CSS can't style or position it.
 * This renders a button+calendar-panel widget instead (see
 * assets/js/hukkoo-components.js' Date behavior), backed by a hidden
 * `<input type="hidden">` that carries the real 'Y-m-d' value. The PHP
 * side renders the initial month's grid; prev/next navigation re-renders
 * it client-side (the JS duplicates this class's day-grid math rather
 * than round-tripping to the server for every month change).
 *
 * Known limitation: same as Select — a hidden input is excluded from
 * native constraint validation, so `required` only drives the visual
 * label asterisk.
 *
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  'YYYY-MM-DD' (escaped)
 *   min          string  'YYYY-MM-DD' earliest selectable date
 *   max          string  'YYYY-MM-DD' latest selectable date
 *   placeholder  string  Shown when nothing is selected (escaped)
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Shows the required asterisk on the label
 *   disabled     bool    Disables the field
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Date extends Field
{
    use RendersCalendarGrid;

    protected function render_input(): string
    {
        $color       = $this->args['color'] ?? null;
        $size        = $this->args['size'] ?? 'md';
        $ghost       = (bool) ($this->args['ghost'] ?? false);
        $name        = $this->args['name'] ?? '';
        $value       = $this->args['value'] ?? '';
        $min         = $this->args['min'] ?? null;
        $max         = $this->args['max'] ?? null;
        $placeholder = $this->args['placeholder'] ?? '';
        $disabled    = (bool) ($this->args['disabled'] ?? false);

        $selected = $value !== '' ? \DateTimeImmutable::createFromFormat('Y-m-d', $value) : null;
        $selected = $selected instanceof \DateTimeImmutable ? $selected : null;
        $view     = $selected ?? new \DateTimeImmutable('today');

        // Color lives on the wrapper (not the trigger) so the --hk-field-c
        // custom property it sets also cascades to the calendar panel,
        // which is the trigger's sibling rather than its descendant.
        $wrapper_class = $this->classes(
            $this->bem('date'),
            $color !== null ? $this->bem('field-input', $color) : null
        );

        $trigger_attrs = [
            'type'                    => 'button',
            'id'                      => $name,
            'class'                   => $this->classes(
                $this->bem('field-input'),
                $this->bem('select-trigger'),
                $size !== 'md' ? $this->bem('field-input', $size) : null,
                $ghost ? $this->bem('field-input', 'ghost') : null
            ),
            'aria-haspopup'           => 'dialog',
            'aria-expanded'           => 'false',
            'disabled'                => $disabled,
            'data-hk-dropdown-toggle' => true,
        ];

        $hidden_attrs = [
            'type'                     => 'hidden',
            'name'                     => $name,
            'value'                    => $value,
            'data-hk-date-value-input' => true,
        ];

        $panel_attrs = [
            'role'               => 'dialog',
            'aria-label'         => __('Choose date', 'hukkoo-components'),
            'data-hk-dropdown-menu' => true,
            'data-hk-date-panel' => true,
            'data-hk-date-min'   => $min,
            'data-hk-date-max'   => $max,
            'data-hk-date-value' => $value,
            'data-hk-date-year'  => $view->format('Y'),
            'data-hk-date-month' => $view->format('n'),
        ];

        return sprintf(
            '<div class="%s" data-hk-dropdown>'
                . '<button %s><span class="%s">%s</span><span class="%s" aria-hidden="true"></span></button>'
                . '<input %s />'
                . '<div class="%s" %s hidden>%s</div>'
            . '</div>',
            $wrapper_class,
            $this->attributes($trigger_attrs),
            $this->bem('select-value'),
            $this->text($value !== '' ? $this->display_format($value) : $placeholder),
            $this->bem('select-caret'),
            $this->attributes($hidden_attrs),
            $this->bem('date-panel'),
            $this->attributes($panel_attrs),
            $this->render_calendar_grid((int) $view->format('Y'), (int) $view->format('n'), $value, $min, $max)
        );
    }

    private function display_format(string $value): string
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof \DateTimeImmutable ? $date->format('M j, Y') : $value;
    }
}
