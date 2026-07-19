<?php
/**
 * Hukkoo Components — standalone design-system library.
 *
 * This file has no Plugin Name header on purpose: it is not a WordPress
 * plugin in its own right. A host plugin requires this file directly and
 * boots it explicitly:
 *
 *     require_once __DIR__ . '/hukkoo-component/hukkoo-components.php';
 *
 *     hukkoo_components(__FILE__, [
 *         'showcase' => true,
 *     ])->boot();
 *
 * Multiple Hukkoo plugins each vendor their own copy of this exact file.
 * If more than one is active on the same site, exactly one copy must
 * "win" and actually define the library's classes/functions — whichever
 * copy is newest, not just whichever plugin's main file WordPress happens
 * to require first. See the version-resolution note below hukkoo_components()
 * for how that's handled.
 */

defined('ABSPATH') || exit;

// Every copy appends itself here — safe to run any number of times, since
// it's just an array push, not a class/function declaration. This is
// deliberately NOT resolved yet; see hukkoo_components() below.
global $hukkoo_components_candidates;
$hukkoo_components_candidates ??= [];
$hukkoo_components_candidates[] = [
    'version' => '0.2.0',
    'dir'     => __DIR__,
    'file'    => __FILE__,
];

if (!function_exists('hukkoo_components')) {
    /**
     * Composition root factory. Returns the same Library instance for a
     * given host plugin file so repeated calls (e.g. from different hook
     * callbacks) don't re-merge config or re-register components.
     *
     * Version resolution is intentionally lazy, on first call, rather
     * than at file-require time: WordPress requires each active plugin's
     * main file one at a time, so resolving the moment THIS copy loads
     * would only ever see itself, not any other Hukkoo plugin's copy —
     * whichever happened to load first would always "win," regardless of
     * version. Every host only ever calls hukkoo_components() from its
     * own plugins_loaded hook (or later), and WordPress guarantees every
     * active plugin's main file has already been required by the time
     * plugins_loaded fires for any of them — so by the time this
     * function is actually called, every copy has already appended its
     * candidate above, and comparing versions here is complete and
     * order-independent.
     *
     * Whichever copy's source physically "wins" the function_exists()
     * race above doesn't matter: every copy is byte-identical (same
     * file, vendored via the same sync step), so the logic below always
     * resolves to the newest candidate's directory regardless of which
     * copy's declaration happened to run first.
     */
    function hukkoo_components(string $host_plugin_file, array $config = []): \Hukkoo\Components\Library
    {
        static $resolved = false;

        if (!$resolved) {
            global $hukkoo_components_candidates;

            usort(
                $hukkoo_components_candidates,
                static fn (array $a, array $b): int => version_compare($b['version'], $a['version'])
            );
            $winner = $hukkoo_components_candidates[0];

            define('HUKKOO_COMPONENTS_DIR', $winner['dir']);
            define('HUKKOO_COMPONENTS_FILE', $winner['file']);
            define('HUKKOO_COMPONENTS_VERSION', $winner['version']);

            // URL resolution needs a "how was this file loaded" anchor. A
            // host plugin requires this file from inside its own plugin
            // directory, so plugins_url() resolved against the WINNING
            // copy's file always points at the right place regardless of
            // which host actually shipped the newest version.
            define('HUKKOO_COMPONENTS_URL', rtrim(plugins_url('', HUKKOO_COMPONENTS_FILE), '/'));

            spl_autoload_register(static function (string $class): void {
                // Showcase is checked first: it lives outside src/, and its
                // namespace is otherwise a prefix-match subset of the
                // general Components root.
                $roots = [
                    'Hukkoo\\Components\\Showcase\\' => HUKKOO_COMPONENTS_DIR . '/Showcase/',
                    'Hukkoo\\Components\\'           => HUKKOO_COMPONENTS_DIR . '/src/',
                ];

                foreach ($roots as $prefix => $base_dir) {
                    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                        continue;
                    }

                    $relative = substr($class, strlen($prefix));
                    $path     = $base_dir . str_replace('\\', '/', $relative) . '.php';

                    if (is_file($path)) {
                        require $path;
                    }

                    return;
                }
            });

            $resolved = true;
        }

        return \Hukkoo\Components\Library::instance($host_plugin_file, $config);
    }
}
