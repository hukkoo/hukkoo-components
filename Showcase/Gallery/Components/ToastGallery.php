<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Components\Toast;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\CodeBlock;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

final class ToastGallery implements GalleryInterface
{
    private const LIVE_TOAST_ID = 'hk-gallery-toast-live';

    // Shared between the static "Colors" previews and the "Trigger"
    // buttons, so both sections describe the same variants.
    private const VARIANTS = [
        ['title' => 'Default', 'color' => null, 'message' => 'Something happened.'],
        ['title' => 'Success', 'color' => 'success', 'message' => 'Your changes are saved successfully.'],
        ['title' => 'Error', 'color' => 'error', 'message' => 'Error has occurred while saving changes.'],
        ['title' => 'Warning', 'color' => 'warning', 'message' => 'Username you have entered is invalid.'],
        ['title' => 'Info', 'color' => 'info', 'message' => 'New settings are available on your account.'],
    ];

    public static function slug(): string
    {
        return 'toast';
    }

    public static function label(): string
    {
        return __('Toast', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Colors', 'hukkoo-components'), self::colors()),
                self::trigger_section(),
            ],
            ApiReference::fromReflection(Toast::class)
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function colors(): array
    {
        $examples = [];

        foreach (self::VARIANTS as $variant) {
            $args = array_filter([
                'id'      => 'hk-gallery-toast-' . sanitize_title($variant['title']),
                'color'   => $variant['color'],
                'message' => $variant['message'],
                'static'  => true,
            ], static fn (mixed $value): bool => $value !== null);

            $examples[] = self::example($variant['title'], $args);
        }

        return $examples;
    }

    private static function trigger_section(): string
    {
        $buttons = '';

        foreach (self::VARIANTS as $variant) {
            $onclick = sprintf(
                "window.hkToast('%s', %s, '%s')",
                esc_js($variant['message']),
                $variant['color'] !== null ? "'" . esc_js($variant['color']) . "'" : 'null',
                esc_js(self::LIVE_TOAST_ID)
            );

            $buttons .= (new Button([
                'label' => $variant['title'],
                'color' => $variant['color'] ?? 'neutral',
                'attrs' => ['onclick' => $onclick],
            ]))->render();
        }

        // The single shell every trigger button targets — window.hkToast()
        // fills it in and shows it, then it hides itself again.
        $toast_shell = (new Toast(['id' => self::LIVE_TOAST_ID]))->render();

        $code = <<<'PHP'
(new Toast(['id' => 'my-toast']))->render(); // once per page, anywhere

// From a button's onclick, form submit handler, AJAX success callback, etc:
window.hkToast('Your changes are saved successfully.', 'success', 'my-toast');
PHP;

        return sprintf(
            '<section class="hk-gallery-section" id="toast-trigger">'
                . '<h3 class="hk-gallery-section-heading"><a href="#toast-trigger" class="hk-gallery-section-anchor">%1$s</a></h3>'
                . '<div class="hk-gallery-preview-row">%2$s</div>'
                . '%3$s'
                . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%4$s" '
                . 'data-hk-label-show="%5$s" data-hk-label-hide="%6$s" aria-expanded="false">%5$s</button>'
                . '%7$s'
            . '</section>',
            esc_html__('Trigger', 'hukkoo-components'),
            $buttons,
            $toast_shell,
            esc_attr('hk-gallery-code-toast-trigger'),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, 'hk-gallery-code-toast-trigger')
        );
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        return [
            'title' => $title,
            'html'  => (new Toast($args))->render(),
            'code'  => sprintf('(new Toast(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }
}
