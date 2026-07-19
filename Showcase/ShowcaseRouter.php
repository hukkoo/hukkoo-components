<?php

namespace Hukkoo\Components\Showcase;

defined('ABSPATH') || exit;

/**
 * Maps a slug to a renderer. Deliberately takes $slug as an explicit
 * argument everywhere — this class never reads $_GET/$_REQUEST itself.
 * The one sanitized read of the routing param happens once, in
 * Showcase::render_page(), and is passed down from there.
 */
final class ShowcaseRouter
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function register(string $slug, callable $renderer): void
    {
        $this->routes[$slug] = $renderer;
    }

    public function has(string $slug): bool
    {
        return isset($this->routes[$slug]);
    }

    public function render(string $slug, array $context = []): string
    {
        if (!isset($this->routes[$slug])) {
            $slug = 'home';
        }

        if (!isset($this->routes[$slug])) {
            return '';
        }

        return (string) call_user_func($this->routes[$slug], $context);
    }
}
