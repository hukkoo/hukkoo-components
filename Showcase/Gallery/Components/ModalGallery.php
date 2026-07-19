<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Components\Modal;
use Hukkoo\Components\Html;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;

defined('ABSPATH') || exit;

/**
 * Modal is JS-driven (a `data-hk-modal-open="id"` trigger anywhere on
 * the page opens the matching hidden #id modal — see
 * assets/js/hukkoo-components.js), so every example here pairs one
 * trigger Button with one Modal instance rendered right alongside it,
 * same as the Toast gallery's "Trigger" section.
 */
final class ModalGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'modal';
    }

    public static function label(): string
    {
        return __('Modal', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Basic', 'hukkoo-components'), [
                    self::basic_example(),
                ]),
                GallerySection::render(__('Confirmation', 'hukkoo-components'), [
                    self::confirmation_example(),
                ]),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::size_examples()),
            ],
            ApiReference::fromReflection(Modal::class)
        );
    }

    /** @return array{title: string, html: string, code: string} */
    private static function basic_example(): array
    {
        $id = 'hkgallery-modal-basic';

        $trigger = (new Button([
            'label' => __('Open modal', 'hukkoo-components'),
            'color' => 'primary',
            'attrs' => ['data-hk-modal-open' => $id],
        ]))->render();

        $modal = (new Modal([
            'id'      => $id,
            'title'   => __('Basic modal', 'hukkoo-components'),
            'content' => __('A short piece of supporting text goes here.', 'hukkoo-components'),
            'actions' => Html::raw((new Button([
                'label' => __('Close', 'hukkoo-components'),
                'style' => 'ghost',
                'attrs' => ['data-hk-modal-close' => true],
            ]))->render()),
        ]))->render();

        return [
            'title' => __('Basic', 'hukkoo-components'),
            'html'  => $trigger . $modal,
            'code'  => <<<'PHP'
(new Button([
    'label' => 'Open modal',
    'color' => 'primary',
    'attrs' => ['data-hk-modal-open' => 'my-modal'],
]))->render();

(new Modal([
    'id'      => 'my-modal',
    'title'   => 'Basic modal',
    'content' => 'A short piece of supporting text goes here.',
    'actions' => Html::raw((new Button([
        'label' => 'Close',
        'style' => 'ghost',
        'attrs' => ['data-hk-modal-close' => true],
    ]))->render()),
]))->render();
PHP,
        ];
    }

    /**
     * The pattern the component's own docblock exists to support:
     * Cancel/Confirm actions, the destructive one colored to match, both
     * wired to data-hk-modal-close so either choice dismisses the dialog
     * without any extra JS — a host wires the real handler onto the
     * confirm button's own onclick/data attributes.
     *
     * @return array{title: string, html: string, code: string}
     */
    private static function confirmation_example(): array
    {
        $id = 'hkgallery-modal-confirm';

        $trigger = (new Button([
            'label' => __('Delete item', 'hukkoo-components'),
            'color' => 'error',
            'attrs' => ['data-hk-modal-open' => $id],
        ]))->render();

        $modal = (new Modal([
            'id'      => $id,
            'size'    => 'sm',
            'title'   => __('Delete item?', 'hukkoo-components'),
            'content' => __("This will permanently delete this item. This can't be undone.", 'hukkoo-components'),
            'actions' => Html::raw(
                (new Button([
                    'label' => __('Cancel', 'hukkoo-components'),
                    'style' => 'ghost',
                    'attrs' => ['data-hk-modal-close' => true],
                ]))->render()
                . (new Button([
                    'label' => __('Delete', 'hukkoo-components'),
                    'color' => 'error',
                    'attrs' => ['data-hk-modal-close' => true],
                ]))->render()
            ),
        ]))->render();

        return [
            'title' => __('Delete confirmation', 'hukkoo-components'),
            'html'  => $trigger . $modal,
            'code'  => <<<'PHP'
(new Button([
    'label' => 'Delete item',
    'color' => 'error',
    'attrs' => ['data-hk-modal-open' => 'confirm-delete'],
]))->render();

(new Modal([
    'id'      => 'confirm-delete',
    'size'    => 'sm',
    'title'   => 'Delete item?',
    'content' => "This will permanently delete this item. This can't be undone.",
    'actions' => Html::raw(
        (new Button(['label' => 'Cancel', 'style' => 'ghost', 'attrs' => ['data-hk-modal-close' => true]]))->render()
        // A real app swaps this attrs array for an onclick/data attribute
        // that runs the actual delete before/instead of just closing.
        . (new Button(['label' => 'Delete', 'color' => 'error', 'attrs' => ['data-hk-modal-close' => true]]))->render()
    ),
]))->render();
PHP,
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function size_examples(): array
    {
        $sizes    = ['sm', 'md', 'lg'];
        $examples = [];

        foreach ($sizes as $size) {
            $id = 'hkgallery-modal-' . $size;

            $trigger = (new Button([
                'label' => strtoupper($size),
                'attrs' => ['data-hk-modal-open' => $id],
            ]))->render();

            $modal = (new Modal([
                'id'      => $id,
                'size'    => $size,
                'title'   => sprintf(__('%s modal', 'hukkoo-components'), strtoupper($size)),
                'content' => __('A short piece of supporting text goes here.', 'hukkoo-components'),
                'actions' => Html::raw((new Button([
                    'label' => __('Close', 'hukkoo-components'),
                    'style' => 'ghost',
                    'attrs' => ['data-hk-modal-close' => true],
                ]))->render()),
            ]))->render();

            $examples[] = [
                'title' => strtoupper($size),
                'html'  => $trigger . $modal,
                'code'  => sprintf(
                    "(new Button(['label' => '%s', 'attrs' => ['data-hk-modal-open' => 'my-modal-%s']]))->render();\n"
                        . "(new Modal(['id' => 'my-modal-%s', 'size' => '%s', 'title' => '…', 'content' => '…']))->render();",
                    strtoupper($size),
                    $size,
                    $size,
                    $size
                ),
            ];
        }

        return $examples;
    }
}
