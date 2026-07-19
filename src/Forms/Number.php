<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  (escaped)
 *   min          string  Minimum value
 *   max          string  Maximum value
 *   step         string  Step increment (default: 'any')
 *   placeholder  string  Input placeholder text
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Marks the field as required
 *   disabled     bool    Disables the field
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Number extends Field
{
    protected function render_input(): string
    {
        $attrs = [
            'class'       => $this->field_input_class(),
            'type'        => 'number',
            'name'        => $this->args['name'] ?? '',
            'id'          => $this->args['name'] ?? '',
            'value'       => $this->args['value'] ?? '',
            'min'         => $this->args['min'] ?? null,
            'max'         => $this->args['max'] ?? null,
            'step'        => $this->args['step'] ?? null,
            'placeholder' => $this->args['placeholder'] ?? null,
            'required'    => (bool) ($this->args['required'] ?? false),
            'disabled'    => (bool) ($this->args['disabled'] ?? false),
        ];

        return sprintf('<input %s />', $this->attributes($attrs));
    }
}
