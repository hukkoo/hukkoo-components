<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * Base for concrete field types (Text, Select, Checkbox, …).
 *
 * $args:
 *   name        string  Field name/id (required)
 *   label       string  (escaped)
 *   value       string  (escaped where rendered)
 *   required    bool
 *   error       string  Validation message (escaped)
 *   description string  Help text (escaped)
 *   width       string  'auto'|'full' Spans every column in a Form's 'grid' layout (default: 'auto')
 */
abstract class Field extends Component
{
    abstract protected function render_input(): string;

    /**
     * Resolves the 'hk-field-input' class plus the color/size/ghost
     * modifiers every text-like field shares (Text, Textarea, Number, …).
     * $extra appends block-specific classes (e.g. Textarea's own block
     * class) before the modifiers, so they still sit after 'hk-field-input'
     * in source order.
     */
    protected function field_input_class(string ...$extra): string
    {
        $color = $this->args['color'] ?? null;
        $size  = $this->args['size'] ?? 'md';
        $ghost = (bool) ($this->args['ghost'] ?? false);

        return $this->classes(
            $this->bem('field-input'),
            ...$extra,
            ...[
                $color !== null ? $this->bem('field-input', $color) : null,
                $size !== 'md' ? $this->bem('field-input', $size) : null,
                $ghost ? $this->bem('field-input', 'ghost') : null,
            ]
        );
    }

    public function render(): string
    {
        $name  = $this->args['name'] ?? '';
        $label = $this->args['label'] ?? null;
        $error = $this->args['error'] ?? null;
        $desc  = $this->args['description'] ?? null;

        $class = $this->classes(
            $this->bem('field'),
            ['hk-field--error' => (bool) $error],
            ($this->args['width'] ?? 'auto') === 'full' ? 'hk-field--full' : null
        );

        $labelHtml = $label !== null
            ? sprintf(
                '<label class="%s" for="%s">%s%s</label>',
                $this->classes($this->bem('field-label')),
                esc_attr($name),
                $this->text($label),
                ($this->args['required'] ?? false) ? ' <span class="hk-field-required">*</span>' : ''
            )
            : '';

        $descHtml = $desc !== null
            ? sprintf('<p class="%s">%s</p>', $this->classes($this->bem('field-description')), $this->text($desc))
            : '';

        $errorHtml = $error !== null
            ? sprintf('<p class="%s">%s</p>', $this->classes($this->bem('field-error')), $this->text($error))
            : '';

        return sprintf(
            '<div class="%s">%s%s%s%s</div>',
            $class,
            $labelHtml,
            $this->render_input(),
            $descHtml,
            $errorHtml
        );
    }
}
