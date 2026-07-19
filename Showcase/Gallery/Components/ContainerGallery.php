<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Card;
use Hukkoo\Components\Html;
use Hukkoo\Components\Layout\Container;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;

defined('ABSPATH') || exit;

final class ContainerGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'container';
    }

    public static function label(): string
    {
        return __('Container', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Widths', 'hukkoo-components'), self::widths()),
                GallerySection::render(__('With content', 'hukkoo-components'), [
                    self::usage_example(),
                ]),
            ],
            ApiReference::fromReflection(Container::class)
        );
    }

    /**
     * Each variant is stacked full-width (see the :has() rule in
     * components.css) rather than side by side — three shrink-to-fit
     * boxes in a row all render the same size, which would hide the one
     * thing this section exists to show.
     *
     * @return array<int, array{title: string, html: string, code: string}>
     */
    private static function widths(): array
    {
        return [
            self::example('Narrow', 'narrow', '640px'),
            self::example('Default', 'default', '960px — the default'),
            self::example('Wide', 'wide', '1280px'),
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, string $width, string $caption): array
    {
        $args = $width !== 'default' ? ['width' => $width] : [];

        $content = sprintf(
            '<p class="hk-u-text-muted" style="margin:0">%s <code>%s</code></p>',
            esc_html__('This box is a Container with', 'hukkoo-components'),
            esc_html($caption)
        );

        return [
            'title' => $title,
            'html'  => sprintf(
                '<div class="hk-gallery-container-demo">%s</div>',
                (new Container($args + ['content' => Html::raw($content)]))->render()
            ),
            'code'  => sprintf(
                "(new Container(['content' => \$html%s]))->render();",
                $width !== 'default' ? ", 'width' => '{$width}'" : ''
            ),
        ];
    }

    /**
     * The realistic case: a Container wrapping other rendered components
     * (a Card here) rather than a bare label — Container almost always
     * composes with something, so the demo should show that instead of
     * only the width presets in isolation.
     *
     * @return array{title: string, html: string, code: string}
     */
    private static function usage_example(): array
    {
        $card = (new Card([
            'title'   => __('Page title', 'hukkoo-components'),
            'content' => __('Container centers and caps the width of whatever it wraps — typically a page\'s main content, like this Card.', 'hukkoo-components'),
        ]))->render();

        return [
            'title' => __('Wrapping a Card', 'hukkoo-components'),
            'html'  => sprintf(
                '<div class="hk-gallery-container-demo">%s</div>',
                (new Container(['content' => Html::raw($card)]))->render()
            ),
            'code'  => <<<'PHP'
(new Container([
    'content' => Html::raw((new Card([
        'title'   => 'Page title',
        'content' => '…',
    ]))->render()),
]))->render();
PHP,
        ];
    }
}
