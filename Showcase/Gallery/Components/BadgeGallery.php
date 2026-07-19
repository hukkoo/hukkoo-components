<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Badge;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

final class BadgeGallery implements GalleryInterface
{
    private const COLORS = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];

    public static function slug(): string
    {
        return 'badge';
    }

    public static function label(): string
    {
        return __('Badge', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Colors', 'hukkoo-components'), self::colors()),
                GallerySection::render(__('Soft', 'hukkoo-components'), self::soft()),
                GallerySection::render(__('Outline with dot', 'hukkoo-components'), self::outline_dot()),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::sizes()),
            ],
            ApiReference::fromReflection(Badge::class)
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function colors(): array
    {
        $examples = [];

        foreach (self::COLORS as $color) {
            $examples[] = self::example(ucfirst($color), ['label' => ucfirst($color), 'color' => $color]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function soft(): array
    {
        $examples = [];

        foreach (self::COLORS as $color) {
            $examples[] = self::example(ucfirst($color), ['label' => ucfirst($color), 'color' => $color, 'soft' => true]);
        }

        return $examples;
    }

    /**
     * The status-pill look used by the Table gallery's full demos —
     * white/bordered background, a small colored dot doing the work a
     * solid fill usually would.
     *
     * @return array<int, array{title: string, html: string, code: string}>
     */
    private static function outline_dot(): array
    {
        return [
            self::example('Active', ['label' => 'Active', 'color' => 'success', 'outline' => true, 'dot' => true]),
            self::example('Pending', ['label' => 'Pending', 'color' => 'warning', 'outline' => true, 'dot' => true]),
            self::example('Suspended', ['label' => 'Suspended', 'color' => 'error', 'outline' => true, 'dot' => true]),
            self::example('Draft', ['label' => 'Draft', 'color' => 'neutral', 'outline' => true, 'dot' => true]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function sizes(): array
    {
        return [
            self::example('MD', ['label' => 'MD', 'color' => 'primary']),
            self::example('LG', ['label' => 'LG', 'color' => 'primary', 'size' => 'lg']),
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        return [
            'title' => $title,
            'html'  => (new Badge($args))->render(),
            'code'  => sprintf('(new Badge(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }
}
