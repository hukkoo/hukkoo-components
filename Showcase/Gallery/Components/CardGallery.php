<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Card;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

final class CardGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'card';
    }

    public static function label(): string
    {
        return __('Card', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Basic', 'hukkoo-components'), [
                    self::example('Basic', [
                        'title'   => 'Card title',
                        'content' => 'A short piece of supporting text goes here.',
                    ]),
                ]),
                GallerySection::render(__('With footer', 'hukkoo-components'), [
                    self::example('With footer', [
                        'title'   => 'Card title',
                        'content' => 'A short piece of supporting text goes here.',
                        'footer'  => 'Last updated 2 hours ago.',
                    ]),
                ]),
            ],
            ApiReference::fromReflection(Card::class)
        );
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        return [
            'title' => $title,
            'html'  => (new Card($args))->render(),
            'code'  => sprintf('(new Card(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }
}
