<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;
use Hukkoo\Components\Html;

defined('ABSPATH') || exit;

/**
 * A dialog overlay driven by the existing Modal JS behavior (Escape to
 * close, focus trap, restores focus on close — see
 * assets/js/hukkoo-components.js). Render this once per modal id;
 * any `<button data-hk-modal-open="that-id">` anywhere on the page,
 * including markup rendered later, opens it — the JS hooks off the
 * data attribute via delegation, not a reference captured at render time.
 *
 * $args:
 *   id       string       Unique id other triggers reference via data-hk-modal-open (required)
 *   title    string       Modal heading (escaped)
 *   content  Html|string  Body — Html::raw() for composed component markup, plain string is escaped
 *   actions  Html|string  Footer buttons — same trust rule as content
 *   size     string       'sm'|'md'|'lg'  (default: 'md')
 */
final class Modal extends Component
{
    public function render(): string
    {
        $id      = $this->args['id'] ?? '';
        $title   = $this->args['title'] ?? null;
        $content = $this->args['content'] ?? '';
        $actions = $this->args['actions'] ?? null;
        $size    = $this->args['size'] ?? 'md';

        $title_html = $title !== null
            ? sprintf('<h3 id="%s-title" class="%s">%s</h3>', esc_attr($id), $this->bem('modal-title'), $this->text($title))
            : '';

        $actions_html = $actions !== null
            ? sprintf(
                '<div class="%s">%s</div>',
                $this->bem('modal-actions'),
                $actions instanceof Html ? (string) $actions : $this->text((string) $actions)
            )
            : '';

        $panel_class = $this->classes(
            $this->bem('modal-panel'),
            $size !== 'md' ? $this->bem('modal-panel', $size) : null
        );

        return sprintf(
            '<div id="%s" class="%s" data-hk-modal hidden>'
                . '<div class="%s" role="dialog" aria-modal="true"%s>'
                    . '<button type="button" class="%s" data-hk-modal-close aria-label="%s">×</button>'
                    . '%s'
                    . '<div class="%s">%s</div>'
                    . '%s'
                . '</div>'
            . '</div>',
            esc_attr($id),
            $this->bem('modal'),
            $panel_class,
            $title !== null ? sprintf(' aria-labelledby="%s-title"', esc_attr($id)) : '',
            $this->bem('modal-close'),
            esc_attr__('Close', 'hukkoo-components'),
            $title_html,
            $this->bem('modal-body'),
            $content instanceof Html ? (string) $content : $this->text((string) $content),
            $actions_html
        );
    }
}
