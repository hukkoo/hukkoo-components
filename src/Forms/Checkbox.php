<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * A single checkbox with its label beside it rather than above — the
 * conventional toggle layout, which doesn't fit Field's label-above-input
 * shape, so this extends Component directly instead of Field. Native
 * <input type="checkbox">, tinted via accent-color (well supported)
 * rather than a fully custom-built control.
 *
 * $args:
 *   name        string  Field name/id (required)
 *   label       string  Shown beside the box (escaped)
 *   checked     bool    Initial checked state
 *   value       string  Submitted value when checked (default: '1')
 *   color       string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  accent-color (default: 'primary')
 *   required    bool    Marks the field as required
 *   disabled    bool    Disables the field
 *   description string  Help text (escaped)
 *   width       string  'auto'|'full' Spans every column in a Form's 'grid' layout (default: 'auto')
 */
final class Checkbox extends Component
{
    public function render(): string
    {
        $name  = $this->args['name'] ?? '';
        $label = $this->args['label'] ?? null;
        $color = $this->args['color'] ?? 'primary';
        $desc  = $this->args['description'] ?? null;

        $attrs = [
            'type'     => 'checkbox',
            'class'    => $this->classes($this->bem('checkbox'), $this->bem('checkbox', $color)),
            'name'     => $name,
            'id'       => $name,
            'value'    => $this->args['value'] ?? '1',
            'checked'  => (bool) ($this->args['checked'] ?? false),
            'required' => (bool) ($this->args['required'] ?? false),
            'disabled' => (bool) ($this->args['disabled'] ?? false),
        ];

        $label_html = $label !== null
            ? sprintf('<span class="%s">%s</span>', $this->bem('checkbox-label'), $this->text($label))
            : '';

        $desc_html = $desc !== null
            ? sprintf('<p class="%s">%s</p>', $this->bem('field-description'), $this->text($desc))
            : '';

        $class = $this->classes(
            $this->bem('checkbox-field'),
            ($this->args['width'] ?? 'auto') === 'full' ? 'hk-field--full' : null
        );

        return sprintf(
            '<div class="%s"><label class="%s"><input %s />%s</label>%s</div>',
            $class,
            $this->bem('checkbox-row'),
            $this->attributes($attrs),
            $label_html,
            $desc_html
        );
    }
}
