<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Resolves the library version, filterable so a host plugin can pin or
 * override it (e.g. to bust caches after a host-side rebrand of the CSS).
 */
final class Version
{
    public static function get(): string
    {
        return (string) apply_filters('hukkoo_components_version', HUKKOO_COMPONENTS_VERSION);
    }
}
