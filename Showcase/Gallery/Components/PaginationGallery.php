<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Data\Pagination;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

/**
 * Pagination is purely presentational — every button just carries
 * data-hk-page="N", no built-in JS wires it to anything (see the
 * component's own docblock). The Table gallery's full demos are the
 * worked example of hooking it up to real page-switching; this page
 * just documents the visual states.
 */
final class PaginationGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'pagination';
    }

    public static function label(): string
    {
        return __('Pagination', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Basic', 'hukkoo-components'), [
                    self::example('5 pages', ['current' => 1, 'total' => 5]),
                ]),
                GallerySection::render(__('Current page position', 'hukkoo-components'), [
                    self::example('Near the start', ['current' => 2, 'total' => 20]),
                    self::example('In the middle', ['current' => 10, 'total' => 20]),
                    self::example('Near the end', ['current' => 19, 'total' => 20]),
                ]),
                GallerySection::render(__('Single page', 'hukkoo-components'), [
                    self::example('No pages to flip through', ['current' => 1, 'total' => 1]),
                ]),
            ],
            ApiReference::fromReflection(Pagination::class)
        );
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        return [
            'title' => $title,
            'html'  => (new Pagination($args))->render(),
            'code'  => sprintf('(new Pagination(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }
}
