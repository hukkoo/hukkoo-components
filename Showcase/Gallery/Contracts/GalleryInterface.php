<?php

namespace Hukkoo\Components\Showcase\Gallery\Contracts;

defined('ABSPATH') || exit;

interface GalleryInterface
{
    public static function slug(): string;

    public static function label(): string;

    public static function render(): string;
}
