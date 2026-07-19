<?php

namespace Hukkoo\Components\Showcase\Helpers;

defined('ABSPATH') || exit;

final class Template
{
    public static function wrap(string $nav, string $content): string
    {
        return sprintf(
            '<div class="hk-showcase-layout"><aside class="hk-showcase-sidebar">%s</aside><main class="hk-showcase-content">%s</main></div>',
            $nav,
            $content
        );
    }
}
