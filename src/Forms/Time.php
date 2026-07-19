<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Forms\Concerns\RendersTimeColumns;

defined('ABSPATH') || exit;

/**
 * A native <input type="time">'s popup is OS-drawn — same problem as
 * Select's <option> list and Date's calendar. This renders a
 * button+time-columns widget instead (see assets/js/hukkoo-components.js'
 * Time behavior), backed by a hidden `<input type="hidden">` carrying
 * the real 'HH:MM' value. Minutes step in 5s to keep the column a
 * manageable length — see RendersTimeColumns.
 *
 * Known limitation: same as Select/Date — a hidden input is excluded
 * from native constraint validation, so `required` only drives the
 * visual label asterisk.
 *
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  'HH:MM' (escaped)
 *   placeholder  string  Shown when nothing is selected (escaped)
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Shows the required asterisk on the label
 *   disabled     bool    Disables the field
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Time extends Field
{
    use RendersTimeColumns;

    protected function render_input(): string
    {
        $color       = $this->args['color'] ?? null;
        $size        = $this->args['size'] ?? 'md';
        $ghost       = (bool) ($this->args['ghost'] ?? false);
        $name        = $this->args['name'] ?? '';
        $value       = $this->args['value'] ?? '';
        $placeholder = $this->args['placeholder'] ?? '';
        $disabled    = (bool) ($this->args['disabled'] ?? false);

        [$hour, $minute] = $value !== '' ? array_pad(explode(':', $value), 2, '00') : ['00', '00'];

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
            'data-hk-time-value-input' => true,
        ];

        $panel_attrs = [
            'role'                  => 'dialog',
            'aria-label'            => __('Choose time', 'hukkoo-components'),
            'data-hk-dropdown-menu' => true,
            'data-hk-time-panel'    => true,
            'data-hk-time-hour'     => $hour,
            'data-hk-time-minute'   => $minute,
        ];

        return sprintf(
            '<div class="%s" data-hk-dropdown>'
                . '<button %s><span class="%s">%s</span><span class="%s" aria-hidden="true"></span></button>'
                . '<input %s />'
                . '<div class="%s" %s hidden>%s'
                    . '<button type="button" class="%s" data-hk-time-done>%s</button>'
                . '</div>'
            . '</div>',
            $wrapper_class,
            $this->attributes($trigger_attrs),
            $this->bem('select-value'),
            $this->text($value !== '' ? $value : $placeholder),
            $this->bem('select-caret'),
            $this->attributes($hidden_attrs),
            $this->bem('date-panel'),
            $this->attributes($panel_attrs),
            $this->render_time_columns($hour, $minute),
            $this->bem('time-done'),
            esc_html__('Done', 'hukkoo-components')
        );
    }
}
