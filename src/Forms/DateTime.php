<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Forms\Concerns\RendersCalendarGrid;
use Hukkoo\Components\Forms\Concerns\RendersTimeColumns;

defined('ABSPATH') || exit;

/**
 * Combines Date's calendar grid and Time's hour/minute columns in one
 * panel, since a native <input type="datetime-local">'s popup has the
 * same unstylable-popup problem as both. Picking a day or a time updates
 * that part only — the panel stays open (there's no single click that
 * unambiguously means "done" the way a day click does for Date alone),
 * closed instead via the "Done" button (see assets/js/hukkoo-components.js'
 * DateTime behavior).
 *
 * Known limitations:
 *   - `min`/`max` only gate the date grid (day-level), not the time
 *     columns — a full day+time boundary would need per-selection
 *     re-validation across both columns, deferred until actually needed.
 *   - Same hidden-input constraint-validation gap as Select/Date/Time:
 *     `required` only drives the visual label asterisk.
 *
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  'YYYY-MM-DDTHH:MM' (escaped)
 *   min          string  'YYYY-MM-DD[THH:MM]' earliest selectable date (time part ignored)
 *   max          string  'YYYY-MM-DD[THH:MM]' latest selectable date (time part ignored)
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Shows the required asterisk on the label
 *   disabled     bool    Disables the field
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class DateTime extends Field
{
    use RendersCalendarGrid;
    use RendersTimeColumns;

    protected function render_input(): string
    {
        $color    = $this->args['color'] ?? null;
        $size     = $this->args['size'] ?? 'md';
        $ghost    = (bool) ($this->args['ghost'] ?? false);
        $name     = $this->args['name'] ?? '';
        $value    = $this->args['value'] ?? '';
        $min      = $this->date_part($this->args['min'] ?? null);
        $max      = $this->date_part($this->args['max'] ?? null);
        $disabled = (bool) ($this->args['disabled'] ?? false);

        [$date_part, $time_part] = $value !== '' ? array_pad(explode('T', $value), 2, '') : ['', ''];
        [$hour, $minute]         = $time_part !== '' ? array_pad(explode(':', $time_part), 2, '00') : ['00', '00'];

        $selected = $date_part !== '' ? \DateTimeImmutable::createFromFormat('Y-m-d', $date_part) : null;
        $selected = $selected instanceof \DateTimeImmutable ? $selected : null;
        $view     = $selected ?? new \DateTimeImmutable('today');

        // Color lives on the wrapper (not the trigger) so the --hk-field-c
        // custom property it sets also cascades to the panel, which is
        // the trigger's sibling rather than its descendant.
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
            'role'                    => 'dialog',
            'aria-label'              => __('Choose date and time', 'hukkoo-components'),
            'data-hk-dropdown-menu'   => true,
            'data-hk-date-panel'      => true,
            'data-hk-time-panel'      => true,
            'data-hk-datetime-combo'  => true,
            'data-hk-date-min'        => $min,
            'data-hk-date-max'        => $max,
            'data-hk-date-value'      => $date_part,
            'data-hk-date-year'       => $view->format('Y'),
            'data-hk-date-month'      => $view->format('n'),
            'data-hk-time-hour'       => $hour,
            'data-hk-time-minute'     => $minute,
        ];

        return sprintf(
            '<div class="%s" data-hk-dropdown>'
                . '<button %s><span class="%s">%s</span><span class="%s" aria-hidden="true"></span></button>'
                . '<input %s />'
                . '<div class="%s" %s hidden>%s%s'
                    . '<button type="button" class="%s" data-hk-time-done>%s</button>'
                . '</div>'
            . '</div>',
            $wrapper_class,
            $this->attributes($trigger_attrs),
            $this->bem('select-value'),
            $this->text($value !== '' ? $this->display_format($date_part, $hour, $minute) : ($this->args['placeholder'] ?? '')),
            $this->bem('select-caret'),
            $this->attributes($hidden_attrs),
            $this->bem('date-panel'),
            $this->attributes($panel_attrs),
            $this->render_calendar_grid((int) $view->format('Y'), (int) $view->format('n'), $date_part, $min, $max),
            $this->render_time_columns($hour, $minute),
            $this->bem('time-done'),
            esc_html__('Done', 'hukkoo-components')
        );
    }

    private function date_part(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return explode('T', $value)[0];
    }

    private function display_format(string $date_part, string $hour, string $minute): string
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $date_part);

        return $date instanceof \DateTimeImmutable
            ? sprintf('%s, %s:%s', $date->format('M j, Y'), $hour, $minute)
            : $date_part;
    }
}
