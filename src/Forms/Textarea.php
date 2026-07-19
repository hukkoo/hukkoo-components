<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * $args:
 *   name         string  Field name/id (required)
 *   label        string  (escaped)
 *   value        string  (escaped)
 *   rows         int     Visible row count (default: 4)
 *   placeholder  string  Input placeholder text
 *   color        string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size         string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost        bool    Transparent, borderless until focused
 *   required     bool    Marks the field as required
 *   disabled     bool    Disables the field
 *   error        string  Validation message (escaped)
 *   description  string  Help text (escaped)
 */
final class Textarea extends Field
{
    protected function render_input(): string
    {
        $attrs = [
            'class'       => $this->field_input_class($this->bem('textarea')),
            'name'        => $this->args['name'] ?? '',
            'id'          => $this->args['name'] ?? '',
            'rows'        => (string) ($this->args['rows'] ?? 4),
            'placeholder' => $this->args['placeholder'] ?? null,
            'required'    => (bool) ($this->args['required'] ?? false),
            'disabled'    => (bool) ($this->args['disabled'] ?? false),
        ];

        return sprintf('<textarea %s>%s</textarea>', $this->attributes($attrs), $this->text($this->args['value'] ?? ''));
    }
}
