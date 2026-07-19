<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * A group of radio buttons sharing one `name` — same reasoning as
 * Checkbox for extending Component directly rather than Field. Native
 * <input type="radio">, tinted via accent-color.
 *
 * $args:
 *   name        string  Shared field name (required)
 *   label       string  Group legend, shown above the options (escaped)
 *   options     array   [value => label] pairs, one radio per entry (escaped)
 *   value       string  Currently selected value
 *   color       string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  accent-color (default: 'primary')
 *   required    bool    Marks the group as required
 *   disabled    bool    Disables every option
 *   error       string  Validation message (escaped)
 *   description string  Help text (escaped)
 *   width       string  'auto'|'full' Spans every column in a Form's 'grid' layout (default: 'auto')
 */
final class Radio extends Component
{
    public function render(): string
    {
        $name     = $this->args['name'] ?? '';
        $label    = $this->args['label'] ?? null;
        $options  = $this->args['options'] ?? [];
        $value    = $this->args['value'] ?? '';
        $color    = $this->args['color'] ?? 'primary';
        $required = (bool) ($this->args['required'] ?? false);
        $disabled = (bool) ($this->args['disabled'] ?? false);
        $error    = $this->args['error'] ?? null;
        $desc     = $this->args['description'] ?? null;

        $legend_html = $label !== null
            ? sprintf('<span class="%s">%s</span>', $this->bem('field-label'), $this->text($label))
            : '';

        $options_html = '';
        foreach ($options as $option_value => $option_label) {
            $attrs = [
                'type'     => 'radio',
                'class'    => $this->classes($this->bem('radio'), $this->bem('radio', $color)),
                'name'     => $name,
                'id'       => $name . '_' . sanitize_key((string) $option_value),
                'value'    => (string) $option_value,
                'checked'  => (string) $option_value === (string) $value,
                'required' => $required,
                'disabled' => $disabled,
            ];

            $options_html .= sprintf(
                '<label class="%s"><input %s /><span class="%s">%s</span></label>',
                $this->bem('radio-row'),
                $this->attributes($attrs),
                $this->bem('checkbox-label'),
                $this->text($option_label)
            );
        }

        $desc_html = $desc !== null
            ? sprintf('<p class="%s">%s</p>', $this->bem('field-description'), $this->text($desc))
            : '';

        $error_html = $error !== null
            ? sprintf('<p class="%s">%s</p>', $this->bem('field-error'), $this->text($error))
            : '';

        $class = $this->classes(
            $this->bem('radio-group'),
            ($this->args['width'] ?? 'auto') === 'full' ? 'hk-field--full' : null
        );

        return sprintf(
            '<fieldset class="%s">%s<div class="%s">%s</div>%s%s</fieldset>',
            $class,
            $legend_html,
            $this->bem('radio-options'),
            $options_html,
            $desc_html,
            $error_html
        );
    }
}
