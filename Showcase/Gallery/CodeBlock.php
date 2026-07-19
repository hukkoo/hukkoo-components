<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

final class CodeBlock
{
    /**
     * $id, when set, makes this block a disclosure panel: hidden by
     * default, toggled by a `data-hk-disclosure-toggle="$id"` trigger
     * (see GallerySection and assets/js/hukkoo-components.js).
     */
    public static function render(string $code, ?string $id = null): string
    {
        return sprintf(
            '<pre class="hk-code-block"%s%s><code>%s</code></pre>',
            $id !== null ? sprintf(' id="%s"', esc_attr($id)) : '',
            $id !== null ? ' hidden' : '',
            CodeHighlighter::render($code)
        );
    }
}
