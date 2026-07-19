<?php

namespace Hukkoo\Components\Showcase\Pages;

use Hukkoo\Components\Showcase\Gallery\Registry;

defined('ABSPATH') || exit;

final class Homepage
{
    // Short blurbs for the landing grid — kept here rather than on
    // GalleryInterface since it's homepage copy, not something every
    // gallery is required to declare. A slug missing here just renders
    // its card without a description instead of breaking.
    private const DESCRIPTIONS = [
        'badge'       => 'Small status/label pills — solid, soft, or outline with a dot indicator.',
        'button'      => 'Solid, outline, ghost and link buttons with color, size, shape and loading states.',
        'card'        => 'A simple content container with an optional footer.',
        'container'   => 'A centered, width-constrained wrapper for page content.',
        'crud-table'  => 'A searchable, sortable table with an Add button and per-row View/Edit/Delete actions.',
        'form'        => 'Wraps a list of fields into a real <form> with a nonce and submit button.',
        'icon-button' => 'A square, icon-only button — reuses Button\'s own color, style and size modifiers.',
        'input'       => 'Text, textarea, number, select and other form field types.',
        'modal'       => 'A dialog overlay for confirmations and focused tasks, with focus trap and Escape to close.',
        'pagination'  => 'A prev/numbered/next button group, with ellipsis truncation for long page ranges.',
        'table'       => 'Sortable, paginated data tables with row actions.',
        'toast'       => 'Transient notification cards for success, error, warning and info messages.',
    ];

    public static function render(): string
    {
        $galleries = Registry::all();

        $cards = '';
        foreach ($galleries as $slug => $class) {
            $cards .= sprintf(
                '<a class="hk-showcase-home-card" href="%s">'
                    . '<h3>%s</h3>'
                    . '<p>%s</p>'
                . '</a>',
                esc_url(add_query_arg('tab', $slug)),
                esc_html($class::label()),
                esc_html(self::DESCRIPTIONS[$slug] ?? '')
            );
        }

        return sprintf(
            '<div class="hk-showcase-home">'
                . '<div class="hk-showcase-home-intro">'
                    . '<h2>%s</h2>'
                    . '<p>%s</p>'
                . '</div>'
                . '<div class="hk-showcase-home-grid">%s</div>'
            . '</div>',
            esc_html__('Component library', 'hukkoo-components'),
            esc_html(sprintf(
                /* translators: %d: number of documented components */
                __(
                    "Pick a component below to see live, working examples and an API reference generated straight from the component's own docblock — %d documented so far.",
                    'hukkoo-components'
                ),
                count($galleries)
            )),
            $cards
        );
    }
}
