<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

/**
 * Auto-discovers *Gallery.php files under Gallery/Components/ — adding a
 * new gallery is "drop the file here", not "also register it somewhere".
 */
final class Registry
{
    /** @var array<string, class-string<Contracts\GalleryInterface>> */
    private static array $galleries = [];
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        $files = glob(HUKKOO_COMPONENTS_DIR . '/Showcase/Gallery/Components/*Gallery.php') ?: [];

        foreach ($files as $file) {
            $class = 'Hukkoo\\Components\\Showcase\\Gallery\\Components\\' . basename($file, '.php');

            if (!class_exists($class) || !is_a($class, Contracts\GalleryInterface::class, true)) {
                continue;
            }

            self::$galleries[$class::slug()] = $class;
        }

        self::$galleries = apply_filters('hukkoo_components_showcase_galleries', self::$galleries);
    }

    /** @return array<string, class-string<Contracts\GalleryInterface>> */
    public static function all(): array
    {
        if (!self::$booted) {
            self::boot();
        }

        return self::$galleries;
    }
}
