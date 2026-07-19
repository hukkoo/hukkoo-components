<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  (escaped)
 *   type         string  'text'|'email'|'password'|'tel'|'url'|…  (default: 'text')
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   placeholder  string  Input placeholder text
 *   required     bool    Marks the field as required
 *   disabled     bool    Disables the input
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Text extends Field
{
    protected function render_input(): string
    {
        $attrs = [
            'class'       => $this->field_input_class(),
            'type'        => $this->args['type'] ?? 'text',
            'name'        => $this->args['name'] ?? '',
            'id'          => $this->args['name'] ?? '',
            'value'       => $this->args['value'] ?? '',
            'placeholder' => $this->args['placeholder'] ?? null,
            'required'    => (bool) ($this->args['required'] ?? false),
            'disabled'    => (bool) ($this->args['disabled'] ?? false),
        ];

        return sprintf('<input %s />', $this->attributes($attrs));
    }
}
