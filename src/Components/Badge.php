<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * $args:
 *   label    string  Badge text (required, escaped)
 *   color    string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  (default: 'neutral')
 *   size     string  'md'|'lg'  (default: 'md')
 *   soft     bool    Tinted background instead of solid fill
 *   outline  bool    Surface background with a border instead of a fill — color only shows in the dot/text
 *   dot      bool    Prefixes a small colored status dot (e.g. an "Active" indicator)
 */
final class Badge extends Component
{
    public function render(): string
    {
        $label   = $this->args['label'] ?? '';
        $color   = $this->args['color'] ?? 'neutral';
        $size    = $this->args['size'] ?? 'md';
        $soft    = (bool) ($this->args['soft'] ?? false);
        $outline = (bool) ($this->args['outline'] ?? false);
        $dot     = (bool) ($this->args['dot'] ?? false);

        $class = $this->classes(
            $this->bem('badge'),
            $this->bem('badge', $color),
            $size !== 'md' ? $this->bem('badge', $size) : null,
            ['hk-badge--soft' => $soft, 'hk-badge--outline' => $outline]
        );

        $dot_html = $dot ? '<span class="hk-badge-dot" aria-hidden="true"></span>' : '';

        return sprintf('<span class="%s">%s%s</span>', $class, $dot_html, $this->text($label));
    }
}
