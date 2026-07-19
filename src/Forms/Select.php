<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * A native <select>'s <option> list is an OS-drawn popup — CSS can't
 * style it or position it "below the field". This renders a
 * button+listbox widget instead (see assets/js/hukkoo-components.js'
 * Select behavior), backed by a hidden `<input type="hidden">` that
 * carries the real value for form submission.
 *
 * Known limitation: the `required` arg only drives the visual label
 * asterisk — a hidden input is excluded from native constraint
 * validation per the HTML spec, so the browser won't block submission
 * of an empty required select on its own.
 *
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  Selected option value (escaped)
 *   options      array   [value => label] pairs rendered as listbox options (escaped)
 *   placeholder  string  Shown when nothing is selected (escaped)
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Shows the required asterisk on the label
 *   disabled     bool    Disables the select
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Select extends Field
{
    protected function render_input(): string
    {
        $color       = $this->args['color'] ?? null;
        $size        = $this->args['size'] ?? 'md';
        $ghost       = (bool) ($this->args['ghost'] ?? false);
        $name        = $this->args['name'] ?? '';
        $value       = $this->args['value'] ?? '';
        $options     = $this->args['options'] ?? [];
        $placeholder = $this->args['placeholder'] ?? '';
        $disabled    = (bool) ($this->args['disabled'] ?? false);

        $selected_label = $placeholder;
        foreach ($options as $option_value => $option_label) {
            if ((string) $option_value === (string) $value) {
                $selected_label = $option_label;
                break;
            }
        }

        // Color lives on the wrapper (not the trigger) so the --hk-field-c
        // custom property it sets also cascades to the option list, which
        // is the trigger's sibling rather than its descendant.
        $wrapper_class = $this->classes(
            $this->bem('select'),
            $color !== null ? $this->bem('field-input', $color) : null
        );

        $trigger_attrs = [
            'type'                     => 'button',
            'id'                       => $name,
            'class'                    => $this->classes(
                $this->bem('field-input'),
                $this->bem('select-trigger'),
                $size !== 'md' ? $this->bem('field-input', $size) : null,
                $ghost ? $this->bem('field-input', 'ghost') : null
            ),
            'aria-haspopup'            => 'listbox',
            'aria-expanded'            => 'false',
            'disabled'                 => $disabled,
            'data-hk-dropdown-toggle'  => true,
        ];

        $options_html = '';
        foreach ($options as $option_value => $option_label) {
            $is_selected = (string) $option_value === (string) $value;

            $options_html .= sprintf(
                '<li class="%s" role="option" tabindex="-1" aria-selected="%s" data-hk-select-option="%s">%s</li>',
                $this->classes(
                    $this->bem('select-option'),
                    ['hk-select-option--selected' => $is_selected]
                ),
                $is_selected ? 'true' : 'false',
                esc_attr((string) $option_value),
                $this->text($option_label)
            );
        }

        $hidden_attrs = [
            'type'                   => 'hidden',
            'name'                   => $name,
            'value'                  => $value,
            'data-hk-select-value-input' => true,
        ];

        return sprintf(
            '<div class="%s" data-hk-dropdown>'
                . '<button %s><span class="%s">%s</span><span class="%s" aria-hidden="true"></span></button>'
                . '<input %s />'
                . '<ul class="%s" role="listbox" data-hk-dropdown-menu hidden>%s</ul>'
            . '</div>',
            $wrapper_class,
            $this->attributes($trigger_attrs),
            $this->bem('select-value'),
            $this->text($selected_label),
            $this->bem('select-caret'),
            $this->attributes($hidden_attrs),
            $this->bem('select-menu'),
            $options_html
        );
    }
}
