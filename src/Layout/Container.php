<?php

namespace Hukkoo\Components\Layout;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * Max-width content wrapper.
 *
 * $args:
 *   content string|Html  Inner HTML (required)
 *   width   string        'narrow'|'default'|'wide'  (default: 'default')
 */
final class Container extends Component
{
    public function render(): string
    {
        $width   = $this->args['width'] ?? 'default';
        $content = $this->args['content'] ?? '';

        $class = $this->classes(
            $this->bem('container'),
            $width !== 'default' ? $this->bem('container', $width) : null
        );

        // 'content' is composed HTML (typically other components' rendered
        // output), not raw user data — it's trusted by construction, the
        // same way it would be if this were a plain PHP template. Actual
        // user-supplied values belong in leaf components (Button label,
        // Table cell, …), which route through $this->text()/cell().
        return sprintf('<div class="%s">%s</div>', $class, (string) $content);
    }
}
