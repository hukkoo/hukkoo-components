<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * $args:
 *   title   string        Card heading (escaped)
 *   content string|Html   Body — composed HTML, trusted (see Component docblock)
 *   footer  string|Html   Optional footer — composed HTML, trusted
 */
final class Card extends Component
{
    public function render(): string
    {
        $title   = $this->args['title'] ?? null;
        $content = $this->args['content'] ?? '';
        $footer  = $this->args['footer'] ?? null;

        $class = $this->classes($this->bem('card'));

        $header = $title !== null
            ? sprintf('<div class="%s"><h3 class="%s">%s</h3></div>',
                $this->classes($this->bem('card-header')),
                $this->classes($this->bem('card-title')),
                $this->text($title)
              )
            : '';

        $footerHtml = $footer !== null
            ? sprintf('<div class="%s">%s</div>', $this->classes($this->bem('card-footer')), (string) $footer)
            : '';

        return sprintf(
            '<div class="%s">%s<div class="%s">%s</div>%s</div>',
            $class,
            $header,
            $this->classes($this->bem('card-body')),
            (string) $content,
            $footerHtml
        );
    }
}
