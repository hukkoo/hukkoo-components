<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;
use Hukkoo\Components\Html;

defined('ABSPATH') || exit;

/**
 * A square, icon-only button. Reuses Button's own CSS classes (color,
 * style, size modifiers) rather than duplicating them — visually it IS
 * a Button with shape=square and an icon instead of a text label.
 *
 * $args:
 *   icon      Html    Raw SVG markup (required) — wrap in Html::raw(); a
 *                      plain string is escaped instead of rendered, since
 *                      icon markup is exactly the kind of leaf value the
 *                      escaping convention treats as untrusted by default
 *   label     string  Accessible name (required, escaped into aria-label, never shown)
 *   color     string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  (default: none, plain button)
 *   style     string  'outline'|'dash'|'soft'|'ghost'|'link'  (default: solid fill)
 *   size      string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   disabled  bool
 *   type      string  'button'|'submit'|'reset'  (default: 'button')
 *   attrs     array   Extra HTML attributes merged in as-is (still escaped)
 */
final class IconButton extends Component
{
    public function render(): string
    {
        $icon     = $this->args['icon'] ?? null;
        $label    = $this->args['label'] ?? '';
        $color    = $this->args['color'] ?? null;
        $style    = $this->args['style'] ?? null;
        $size     = $this->args['size'] ?? 'md';
        $disabled = (bool) ($this->args['disabled'] ?? false);
        $type     = $this->args['type'] ?? 'button';
        $extra    = $this->args['attrs'] ?? [];

        $class = $this->classes(
            $this->bem('button'),
            $this->bem('button', 'square'),
            $color !== null ? $this->bem('button', $color) : null,
            $style !== null ? $this->bem('button', $style) : null,
            $size !== 'md' ? $this->bem('button', $size) : null
        );

        $attrs = array_merge([
            'class'      => $class,
            'type'       => $type,
            'disabled'   => $disabled,
            'aria-label' => $label,
        ], $extra);

        return sprintf(
            '<button %s>%s</button>',
            $this->attributes($attrs),
            $icon instanceof Html ? (string) $icon : $this->text((string) $icon)
        );
    }
}
