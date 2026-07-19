<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

/**
 * Renders one documentation section: an anchor-linkable heading, every
 * item's live output together in a single preview row (daisyUI-style —
 * "Colors" shows all 8 buttons side by side, not one card per button),
 * and a single toggle that reveals every item's code underneath.
 */
final class GallerySection
{
    /** @param array<int, array{title: string, html: string, code: string}> $items */
    public static function render(string $heading, array $items): string
    {
        $slug     = sanitize_title($heading);
        $panel_id = 'hk-gallery-code-' . $slug;

        $preview = '';
        foreach ($items as $item) {
            $preview .= sprintf(
                '<div class="hk-gallery-preview-item" title="%s">%s</div>',
                esc_attr($item['title']),
                $item['html']
            );
        }

        $code = implode("\n", array_map(
            static fn (array $item): string => $item['code'],
            $items
        ));

        return sprintf(
            '<section class="hk-gallery-section" id="%1$s">'
            . '<h3 class="hk-gallery-section-heading"><a href="#%1$s" class="hk-gallery-section-anchor">%2$s</a></h3>'
            . '<div class="hk-gallery-preview-row">%3$s</div>'
            . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%4$s" '
            . 'data-hk-label-show="%5$s" data-hk-label-hide="%6$s" aria-expanded="false">%5$s</button>'
            . '%7$s'
            . '</section>',
            esc_attr($slug),
            esc_html($heading),
            $preview,
            esc_attr($panel_id),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, $panel_id)
        );
    }
}
