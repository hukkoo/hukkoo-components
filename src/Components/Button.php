<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * $args:
 *   label     string  Button text (required, escaped)
 *   color     string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Semantic fill color (default: none, plain button)
 *   style     string  'outline'|'dash'|'soft'|'ghost'|'link'  Visual treatment layered over the color (default: solid fill)
 *   size      string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   shape     string  'square'|'circle'|'wide'|'block'  Sizing/layout modifier
 *   type      string  'button'|'submit'|'reset'  (default: 'button')
 *   active    bool    Forces the pressed/active visual state
 *   loading   bool    Shows a spinner in place of interaction and implies disabled
 *   disabled  bool    Disables the button
 *   url       string  If set, renders an <a> styled as a button instead of <button>
 *   attrs     array   Extra HTML attributes merged in as-is (still escaped)
 */
final class Button extends Component
{
    public function render(): string
    {
        $label    = $this->args['label'] ?? '';
        $color    = $this->args['color'] ?? null;
        $style    = $this->args['style'] ?? null;
        $size     = $this->args['size'] ?? 'md';
        $shape    = $this->args['shape'] ?? null;
        $type     = $this->args['type'] ?? 'button';
        $active   = (bool) ($this->args['active'] ?? false);
        $loading  = (bool) ($this->args['loading'] ?? false);
        $disabled = $loading || (bool) ($this->args['disabled'] ?? false);
        $url      = $this->args['url'] ?? null;
        $extra    = $this->args['attrs'] ?? [];

        $class = $this->classes(
            $this->bem('button'),
            $color !== null ? $this->bem('button', $color) : null,
            $style !== null ? $this->bem('button', $style) : null,
            $size !== 'md' ? $this->bem('button', $size) : null,
            $shape !== null ? $this->bem('button', $shape) : null,
            ['hk-button--active' => $active]
        );

        $content = ($loading ? sprintf('<span class="%s" aria-hidden="true"></span>', $this->bem('button-spinner')) : '')
            . $this->text($label);

        if ($url) {
            $attrs = array_merge([
                'class'         => $class,
                'href'          => $this->url($url),
                'aria-disabled' => $disabled ? 'true' : null,
            ], $extra);

            return sprintf(
                '<a %s>%s</a>',
                $this->attributes($attrs),
                $content
            );
        }

        $attrs = array_merge([
            'class'    => $class,
            'type'     => $type,
            'disabled' => $disabled,
        ], $extra);

        return sprintf(
            '<button %s>%s</button>',
            $this->attributes($attrs),
            $content
        );
    }
}
