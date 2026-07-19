<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * Native <input type="color">. The swatch itself is stylable enough via
 * ::-webkit-color-swatch / ::-moz-color-swatch (unlike Select's <option>
 * or Date's calendar popup), so this stays native rather than a custom
 * widget.
 *
 * $args:
 *   name      string  Field name/id (required)
 *   label     string  (escaped)
 *   value     string  '#rrggbb' (default: '#000000')
 *   size      string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   required  bool    Marks the field as required
 *   disabled  bool    Disables the field
 *   error       string  Validation message (escaped)
 *   description string  Help text (escaped)
 */
final class Color extends Field
{
    protected function render_input(): string
    {
        $size = $this->args['size'] ?? 'md';

        $attrs = [
            'class'    => $this->classes(
                $this->bem('field-input'),
                $this->bem('color-input'),
                $size !== 'md' ? $this->bem('field-input', $size) : null
            ),
            'type'     => 'color',
            'name'     => $this->args['name'] ?? '',
            'id'       => $this->args['name'] ?? '',
            'value'    => $this->args['value'] ?? '#000000',
            'required' => (bool) ($this->args['required'] ?? false),
            'disabled' => (bool) ($this->args['disabled'] ?? false),
        ];

        return sprintf('<input %s />', $this->attributes($attrs));
    }
}
