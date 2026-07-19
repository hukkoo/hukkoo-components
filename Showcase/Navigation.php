<?php

namespace Hukkoo\Components\Showcase;

use Hukkoo\Components\Showcase\Gallery\Registry;
use Hukkoo\Components\Showcase\Pages\Homepage;

defined('ABSPATH') || exit;

final class Navigation
{
    /**
     * Nav grouping is deliberately kept out of GalleryInterface — it's
     * presentation, not something every gallery needs to declare. A slug
     * missing here just falls into the catch-all "Components" group
     * instead of breaking, so a new *Gallery.php file still shows up
     * without also having to update this map.
     */
    private const CATEGORIES = [
        'button'      => 'Actions',
        'icon-button' => 'Actions',
        'badge'       => 'Data display',
        'card'        => 'Data display',
        'table'       => 'Data display',
        'pagination'  => 'Data display',
        'input'       => 'Forms',
        'form'        => 'Forms',
        'container'   => 'Layout',
        'modal'       => 'Feedback',
        'toast'       => 'Feedback',
    ];

    public static function register_routes(ShowcaseRouter $router): void
    {
        $router->register('home', static fn () => Homepage::render());

        foreach (Registry::all() as $slug => $class) {
            $router->register($slug, static fn () => $class::render());
        }
    }

    /**
     * $active_slug is passed in explicitly by the caller (Showcase.php),
     * which is the only place in this tree that reads $_GET.
     */
    public static function render(string $active_slug): string
    {
        $groups = ['' => ['home' => __('Overview', 'hukkoo-components')]];

        foreach (Registry::all() as $slug => $class) {
            $category = self::CATEGORIES[$slug] ?? __('Components', 'hukkoo-components');
            $groups[$category][$slug] = $class::label();
        }

        $html = '';
        foreach ($groups as $category => $items) {
            $links = '';
            foreach ($items as $slug => $label) {
                $links .= sprintf(
                    '<a class="hk-showcase-nav-link%s" href="%s">%s</a>',
                    $slug === $active_slug ? ' hk-showcase-nav-link--active' : '',
                    esc_url(add_query_arg('tab', $slug)),
                    esc_html($label)
                );
            }

            // The group label doubles as a link to the first item in its
            // group — otherwise it's inert text sitting among clickable
            // links, which reads as broken rather than as a plain heading.
            $label_html = $category !== ''
                ? sprintf(
                    '<a class="hk-showcase-nav-group-label" href="%s">%s</a>',
                    esc_url(add_query_arg('tab', array_key_first($items))),
                    esc_html($category)
                )
                : '';

            $html .= sprintf('<div class="hk-showcase-nav-group">%s%s</div>', $label_html, $links);
        }

        return sprintf('<nav class="hk-showcase-nav">%s</nav>', $html);
    }
}
