<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Composition root. One instance per host plugin file, so a host that
 * calls hukkoo_components() more than once (e.g. from separate hook
 * callbacks) doesn't re-merge config or double-register components.
 */
final class Library
{
    /** @var array<string, self> */
    private static array $instances = [];

    private readonly string $host_plugin_file;
    private readonly array $config;
    private bool $booted = false;

    public static function instance(string $host_plugin_file, array $config = []): self
    {
        $key = md5($host_plugin_file);

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($host_plugin_file, $config);
        }

        return self::$instances[$key];
    }

    private function __construct(string $host_plugin_file, array $config)
    {
        $this->host_plugin_file = $host_plugin_file;

        $merged       = array_merge(self::defaults(), $config);
        $this->config = apply_filters('hukkoo_components_config', $merged, $host_plugin_file);
    }

    private static function defaults(): array
    {
        return [
            'showcase'   => false,
            'dark_mode'  => true,
            'icons'      => true,
            'animations' => true,
            'minified'   => !(defined('WP_DEBUG') && WP_DEBUG),
            'rtl'        => function_exists('is_rtl') && is_rtl(),
        ];
    }

    public function config(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Fixed by design, not configurable. CSS class names and custom
     * properties across assets/ are hardcoded to hk- — a per-product
     * prefix would require templating/generating the CSS at build time,
     * which this base intentionally doesn't do. Rather than accept a
     * 'prefix' config value that silently does nothing if changed, the
     * prefix isn't exposed as an option at all.
     */
    public static function prefix(): string
    {
        return 'hk';
    }

    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        }

        $this->booted = true;

        ComponentRegistry::boot();

        $assets = new AssetManager();
        add_action('wp_enqueue_scripts', [$assets, 'enqueue']);
        add_action('admin_enqueue_scripts', [$assets, 'enqueue']);

        if ($this->config('showcase')) {
            (new Showcase\Showcase($this->host_plugin_file))->boot();
        }

        return $this;
    }
}
