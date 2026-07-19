<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * Native <input type="file">. Named FileUpload rather than File to avoid
 * colliding with PHP's own \File-adjacent conventions and to read clearly
 * at the call site. The browse button is styled via ::file-selector-button
 * (well supported, unlike Select's <option> list) so this stays native.
 *
 * $args:
 *   name      string  Field name/id (required)
 *   label     string  (escaped)
 *   accept    string  Comma-separated MIME types/extensions (escaped)
 *   multiple  bool    Allow selecting more than one file
 *   color     string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Browse-button accent (default: none)
 *   size      string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   required  bool    Marks the field as required
 *   disabled  bool    Disables the field
 *   error       string  Validation message (escaped)
 *   description string  Help text (escaped)
 */
final class FileUpload extends Field
{
    protected function render_input(): string
    {
        $color = $this->args['color'] ?? null;
        $size  = $this->args['size'] ?? 'md';

        $attrs = [
            'class'    => $this->classes(
                $this->bem('field-input'),
                $this->bem('file-input'),
                $color !== null ? $this->bem('field-input', $color) : null,
                $size !== 'md' ? $this->bem('field-input', $size) : null
            ),
            'type'     => 'file',
            'name'     => $this->args['name'] ?? '',
            'id'       => $this->args['name'] ?? '',
            'accept'   => $this->args['accept'] ?? null,
            'multiple' => (bool) ($this->args['multiple'] ?? false),
            'required' => (bool) ($this->args['required'] ?? false),
            'disabled' => (bool) ($this->args['disabled'] ?? false),
        ];

        return sprintf('<input %s />', $this->attributes($attrs));
    }
}
