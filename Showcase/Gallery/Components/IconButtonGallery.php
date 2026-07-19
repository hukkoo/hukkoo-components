<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\IconButton;
use Hukkoo\Components\Html;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;

defined('ABSPATH') || exit;

final class IconButtonGallery implements GalleryInterface
{
    // Same edit/delete pair the Table gallery's row actions use — one
    // visual language for icon buttons across the whole showcase.
    private const ICON_EDIT = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
    private const ICON_DELETE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m2 0-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 7h14Z"/></svg>';
    private const ICON_PLUS = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>';

    public static function slug(): string
    {
        return 'icon-button';
    }

    public static function label(): string
    {
        return __('Icon Button', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Colors', 'hukkoo-components'), self::colors()),
                GallerySection::render(__('Styles', 'hukkoo-components'), self::styles()),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::sizes()),
                GallerySection::render(__('States', 'hukkoo-components'), self::states()),
                GallerySection::render(__('Row actions', 'hukkoo-components'), self::row_actions()),
            ],
            ApiReference::fromReflection(IconButton::class)
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function colors(): array
    {
        $colors   = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];
        $examples = [self::example('Default', ['icon' => 'plus', 'label' => 'Add'])];

        foreach ($colors as $color) {
            $examples[] = self::example(ucfirst($color), ['icon' => 'plus', 'label' => 'Add', 'color' => $color]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function styles(): array
    {
        $styles   = ['outline', 'dash', 'soft', 'ghost', 'link'];
        $examples = [];

        foreach ($styles as $style) {
            $examples[] = self::example(ucfirst($style), ['icon' => 'edit', 'label' => 'Edit', 'color' => 'primary', 'style' => $style]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function sizes(): array
    {
        $sizes    = ['xs', 'sm', 'md', 'lg', 'xl'];
        $examples = [];

        foreach ($sizes as $size) {
            $examples[] = self::example(strtoupper($size), ['icon' => 'edit', 'label' => 'Edit', 'color' => 'primary', 'size' => $size]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function states(): array
    {
        return [
            self::example('Disabled', ['icon' => 'delete', 'label' => 'Delete', 'color' => 'error', 'disabled' => true]),
        ];
    }

    /**
     * The everyday case: a compact edit/delete pair, ghost-styled so
     * they sit quietly in a table row until hovered — exactly how the
     * Table gallery's own row actions use this component.
     *
     * @return array<int, array{title: string, html: string, code: string}>
     */
    private static function row_actions(): array
    {
        $html = (new IconButton([
            'icon'  => Html::raw(self::ICON_EDIT),
            'label' => __('Edit', 'hukkoo-components'),
            'style' => 'ghost',
            'size'  => 'sm',
        ]))->render()
            . (new IconButton([
                'icon'  => Html::raw(self::ICON_DELETE),
                'label' => __('Delete', 'hukkoo-components'),
                'style' => 'ghost',
                'color' => 'error',
                'size'  => 'sm',
            ]))->render();

        return [
            [
                'title' => __('Edit / Delete', 'hukkoo-components'),
                'html'  => sprintf('<div class="hk-table-actions">%s</div>', $html),
                'code'  => <<<'PHP'
(new IconButton(['icon' => Html::raw($editIcon), 'label' => 'Edit', 'style' => 'ghost', 'size' => 'sm']))->render();
(new IconButton(['icon' => Html::raw($deleteIcon), 'label' => 'Delete', 'style' => 'ghost', 'color' => 'error', 'size' => 'sm']))->render();
PHP,
            ],
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        $icon_key   = $args['icon'];
        $icon_svg   = match ($icon_key) {
            'edit' => self::ICON_EDIT,
            'delete' => self::ICON_DELETE,
            default => self::ICON_PLUS,
        };
        $args['icon'] = Html::raw($icon_svg);

        return [
            'title' => $title,
            'html'  => (new IconButton($args))->render(),
            'code'  => sprintf("(new IconButton(['icon' => Html::raw(\$svg), 'label' => '%s'%s]))->render();", $args['label'], self::extra_args_snippet($args)),
        ];
    }

    private static function extra_args_snippet(array $args): string
    {
        $extra = array_diff_key($args, ['icon' => true, 'label' => true]);

        if (empty($extra)) {
            return '';
        }

        $pairs = [];
        foreach ($extra as $key => $value) {
            $pairs[] = sprintf("'%s' => %s", $key, is_bool($value) ? ($value ? 'true' : 'false') : "'{$value}'");
        }

        return ', ' . implode(', ', $pairs);
    }
}
