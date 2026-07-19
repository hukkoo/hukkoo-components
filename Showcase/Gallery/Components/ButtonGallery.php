<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

/**
 * Reference gallery: drop a *Gallery.php file in this directory
 * implementing GalleryInterface and Registry::boot()'s glob() picks it up
 * automatically — no manual registration step anywhere.
 */
final class ButtonGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'button';
    }

    public static function label(): string
    {
        return __('Button', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Colors', 'hukkoo-components'), self::colors()),
                GallerySection::render(__('Styles', 'hukkoo-components'), self::styles()),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::sizes()),
                GallerySection::render(__('Shapes', 'hukkoo-components'), self::shapes()),
                GallerySection::render(__('States', 'hukkoo-components'), self::states()),
            ],
            ApiReference::fromReflection(Button::class)
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function colors(): array
    {
        $colors  = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];
        $examples = [self::example('Default', ['label' => 'Default'])];

        foreach ($colors as $color) {
            $examples[] = self::example(ucfirst($color), ['label' => ucfirst($color), 'color' => $color]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function styles(): array
    {
        $styles   = ['outline', 'dash', 'soft', 'ghost', 'link'];
        $examples = [];

        foreach ($styles as $style) {
            $examples[] = self::example(ucfirst($style), [
                'label' => ucfirst($style),
                'color' => 'primary',
                'style' => $style,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function sizes(): array
    {
        $sizes    = ['xs', 'sm', 'md', 'lg', 'xl'];
        $examples = [];

        foreach ($sizes as $size) {
            $examples[] = self::example(strtoupper($size), [
                'label' => strtoupper($size),
                'color' => 'primary',
                'size'  => $size,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function shapes(): array
    {
        return [
            self::example('Square', ['label' => '1', 'color' => 'primary', 'shape' => 'square']),
            self::example('Circle', ['label' => '1', 'color' => 'primary', 'shape' => 'circle']),
            self::example('Wide', ['label' => 'Wide button', 'color' => 'primary', 'shape' => 'wide']),
            self::example('Block', ['label' => 'Block button', 'color' => 'primary', 'shape' => 'block']),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function states(): array
    {
        return [
            self::example('Active', ['label' => 'Active', 'color' => 'primary', 'active' => true]),
            self::example('Disabled', ['label' => 'Disabled', 'color' => 'primary', 'disabled' => true]),
            self::example('Loading', ['label' => 'Loading', 'color' => 'primary', 'loading' => true]),
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        // Instantiate the real component class — the gallery can't fall
        // out of sync with the code because it IS the code.
        return [
            'title' => $title,
            'html'  => (new Button($args))->render(),
            'code'  => sprintf('(new Button(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }
}

