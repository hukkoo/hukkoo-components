<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Enqueues the layered stylesheet chain and the behavior script.
 *
 * House rule: never @import one stylesheet inside another to "bundle"
 * them. WordPress's ?ver= cache-busting only reaches URLs that are
 * actually enqueued — an @import-ed file carries no query string, so a
 * browser can cache it once and never see an update again regardless of
 * version bumps. Every layer below is enqueued and versioned individually.
 */
final class AssetManager
{
    private const STYLE_CHAIN = [
        'hukkoo-components-variables' => 'css/variables.css',
        'hukkoo-components-base'      => 'css/base.css',
        'hukkoo-components-layout'    => 'css/layout.css',
        'hukkoo-components-tokens'    => 'css/components.css',
        'hukkoo-components'           => 'css/utilities.css', // public handle, last in the chain
    ];

    public function enqueue(): void
    {
        $deps = [];

        foreach (self::STYLE_CHAIN as $handle => $path) {
            wp_enqueue_style(
                $handle,
                $this->url($path),
                $deps,
                $this->version($path)
            );
            $deps = [$handle];
        }

        wp_enqueue_script(
            'hukkoo-components',
            $this->url('js/hukkoo-components.js'),
            [],
            $this->version('js/hukkoo-components.js'),
            true
        );
    }

    /**
     * The public style handle a host's own stylesheets should depend on,
     * guaranteeing the full base chain loads first:
     *
     *     wp_enqueue_style('my-product', $url, ['hukkoo-components'], $ver);
     */
    public static function public_style_handle(): string
    {
        return 'hukkoo-components';
    }

    private function url(string $relative_path): string
    {
        return HUKKOO_COMPONENTS_URL . '/assets/' . ltrim($relative_path, '/');
    }

    /**
     * Per-file filemtime() version so editing one layer invalidates only
     * that layer's cache, with no manual version bump required.
     */
    private function version(string $relative_path): string
    {
        $file = HUKKOO_COMPONENTS_DIR . '/assets/' . ltrim($relative_path, '/');

        $mtime = is_file($file) ? (string) filemtime($file) : Version::get();

        return (string) apply_filters('hukkoo_components_asset_version', $mtime, $relative_path);
    }
}
