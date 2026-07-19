<?php

namespace Hukkoo\Components\Showcase;

use Hukkoo\Components\AssetManager;
use Hukkoo\Components\Showcase\Gallery\Registry;
use Hukkoo\Components\Version;

defined('ABSPATH') || exit;

/**
 * Optional, self-contained admin demo/docs app. Only boots when a host
 * enables it via hukkoo_components(FILE, ['showcase' => true]).
 */
final class Showcase
{
    private readonly ShowcaseRouter $router;
    private ?string $hook_suffix = null;

    public function __construct(private readonly string $host_plugin_file)
    {
        $this->router = new ShowcaseRouter();
    }

    public function boot(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void
    {
        $this->hook_suffix = add_menu_page(
            __('Hukkoo Components', 'hukkoo-components'),
            __('Components', 'hukkoo-components'),
            'manage_options',
            'hukkoo-components-showcase',
            [$this, 'render_page'],
            'dashicons-layout',
            80
        );

        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hook_suffix): void
    {
        if ($hook_suffix !== $this->hook_suffix) {
            return;
        }

        (new AssetManager())->enqueue();

        // Product-specific chrome follows the same pattern any host plugin
        // should use for its own CSS: declare the public library handle
        // as a dependency so the full base chain loads first, and version
        // off this file's own mtime rather than sharing the library's.
        $css = HUKKOO_COMPONENTS_DIR . '/Showcase/assets/css/showcase.css';
        wp_enqueue_style(
            'hukkoo-components-showcase',
            HUKKOO_COMPONENTS_URL . '/Showcase/assets/css/showcase.css',
            [AssetManager::public_style_handle()],
            is_file($css) ? (string) filemtime($css) : Version::get()
        );
    }

    public function render_page(): void
    {
        Registry::boot();
        Navigation::register_routes($this->router);

        // The single, sanitized read of the routing param for this whole
        // tree — everything downstream (Navigation, ShowcaseRouter)
        // receives it as an explicit argument instead of reading $_GET.
        $slug = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'home';

        $header = sprintf(
            '<header class="hk-showcase-header">'
                . '<div class="hk-showcase-header-title">'
                    . '<h1>%s</h1>'
                    . '<p>%s</p>'
                . '</div>'
                . '<span class="hk-showcase-version">%s</span>'
            . '</header>',
            esc_html__('Hukkoo Components', 'hukkoo-components'),
            esc_html__('A component library for building consistent WordPress admin UI.', 'hukkoo-components'),
            esc_html('v' . Version::get())
        );

        echo '<div class="hk-page hk-showcase-page">'
            . $header
            . Helpers\Template::wrap(
                Navigation::render($slug),
                $this->router->render($slug)
            )
            . '</div>';
    }
}
