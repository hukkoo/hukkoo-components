<?php

namespace Hukkoo\Components\Components;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * A transient notification card (colored accent border, icon, title,
 * message, dismiss button).
 *
 * Two ways to use it:
 *  - Live/JS-driven (default): render an empty hidden shell once per page,
 *    then call `window.hkToast(message, color, id, title)` from anywhere
 *    to populate and show it; it auto-dismisses (see
 *    assets/js/hukkoo-components.js' Toast behavior).
 *  - Static: pass `message` and it renders already populated and visible
 *    — for server-rendered flash messages, or for docs/preview contexts
 *    that need to show the card without running JS.
 *
 * $args:
 *   id       string  Element id window.hkToast() targets (default: 'hk-toast')
 *   color    string  'neutral'|'success'|'error'|'warning'|'info' (default: 'neutral')
 *   title    string  (escaped; default: a label derived from color, e.g. 'Success')
 *   message  string  (escaped) Presence of this arg switches to the static render
 *   static   bool    Static mode only: lay out inline instead of the fixed corner position
 */
final class Toast extends Component
{
    private const ICONS = [
        'success' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>',
        'error'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>',
        'warning' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v4.25m0 3.25h.01"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.44 4.5 2.62 18a1.3 1.3 0 0 0 1.12 1.95h16.52A1.3 1.3 0 0 0 21.38 18L13.56 4.5a1.3 1.3 0 0 0-2.24 0Z"/></svg>',
        'info'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5v-4.25m0-3.25h.01"/><circle cx="12" cy="12" r="9"/></svg>',
    ];

    private const TITLES = [
        'success' => 'Success',
        'error'   => 'Error',
        'warning' => 'Warning',
        'info'    => 'Info',
    ];

    public function render(): string
    {
        $id      = $this->args['id'] ?? 'hk-toast';
        $color   = $this->args['color'] ?? null;
        $message = $this->args['message'] ?? null;

        if ($message === null) {
            return sprintf(
                '<div id="%s" class="%s" role="status" aria-live="polite" hidden></div>',
                esc_attr($id),
                $this->bem('toast')
            );
        }

        $title  = $this->args['title'] ?? (self::TITLES[$color] ?? 'Notice');
        $icon   = self::ICONS[$color] ?? self::ICONS['info'];
        $static = (bool) ($this->args['static'] ?? false);

        $class = $this->classes(
            $this->bem('toast'),
            $color !== null ? $this->bem('toast', $color) : null,
            'hk-toast--visible',
            $static ? $this->bem('toast', 'static') : null
        );

        return sprintf(
            '<div id="%1$s" class="%2$s" role="status" aria-live="polite">'
                . '<span class="hk-toast-icon" aria-hidden="true">%3$s</span>'
                . '<span class="hk-toast-body">'
                    . '<span class="hk-toast-title">%4$s</span>'
                    . '<span class="hk-toast-message">%5$s</span>'
                . '</span>'
                . '<button type="button" class="hk-toast-close" aria-label="%6$s">'
                    . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>'
                . '</button>'
            . '</div>',
            esc_attr($id),
            $class,
            $icon,
            $this->text($title),
            $this->text($message),
            esc_attr__('Dismiss', 'hukkoo-components')
        );
    }
}
