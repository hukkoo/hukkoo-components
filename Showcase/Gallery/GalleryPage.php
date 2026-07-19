<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

final class GalleryPage
{
    /** @param string[] $sections_html */
    public static function render(string $title, array $sections_html, string $api_reference_html = ''): string
    {
        $api = $api_reference_html !== ''
            ? sprintf(
                '<section class="hk-gallery-section"><h3>%s</h3>%s</section>',
                esc_html__('API Reference', 'hukkoo-components'),
                $api_reference_html
            )
            : '';

        return sprintf(
            '<div class="hk-gallery-page"><h2>%s</h2>%s%s</div>',
            esc_html($title),
            implode('', $sections_html),
            $api
        );
    }
}
