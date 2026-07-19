<?php

namespace Hukkoo\Components\Forms;

use Hukkoo\Components\Component;
use Hukkoo\Components\Components\Button;

defined('ABSPATH') || exit;

/**
 * $args:
 *   action        string  Form action URL (default: current request URL)
 *   method        string  'post'|'get' (default: 'post')
 *   fields        Field[] Rendered as-is via __toString()
 *   layout        string  'stacked'|'grid' Two fields per row when 'grid' — a field passes 'width' => 'full' to span both (default: 'stacked')
 *   nonce_action  string  Passed to wp_nonce_field() (required to render a nonce)
 *   nonce_name    string  Passed to wp_nonce_field() (default: '_wpnonce')
 *   submit_label  string  (escaped)
 */
final class Form extends Component
{
    public function render(): string
    {
        $action = $this->args['action'] ?? '';
        $method = $this->args['method'] ?? 'post';
        $fields = $this->args['fields'] ?? [];
        $layout = $this->args['layout'] ?? 'stacked';

        $fieldsHtml = implode('', array_map(static fn ($field) => (string) $field, $fields));

        // wp_nonce_field() defaults to $echo = true — a trap inside any
        // method contracted to return HTML rather than print it, since the
        // nonce would print immediately (outside the <form> tag being
        // built here) instead of ending up in the returned string. Always
        // pass $echo = false explicitly and splice the return value in.
        $nonce = '';
        if (!empty($this->args['nonce_action'])) {
            $nonce = wp_nonce_field(
                $this->args['nonce_action'],
                $this->args['nonce_name'] ?? '_wpnonce',
                true,
                false
            );
        }

        $submitHtml = '';
        if (!empty($this->args['submit_label'])) {
            $submitHtml = (new Button([
                'type'  => 'submit',
                'color' => 'primary',
                'label' => $this->args['submit_label'],
            ]))->render();

            // The button is a grid child alongside the fields, same as
            // any of them — without this it'd only fill one column
            // instead of sitting full-width below them.
            if ($layout === 'grid') {
                $submitHtml = sprintf('<div class="hk-field--full">%s</div>', $submitHtml);
            }
        }

        return sprintf(
            '<form class="%s" action="%s" method="%s">%s%s%s</form>',
            $this->classes(
                $this->bem('form'),
                $layout === 'grid' ? $this->bem('form', 'grid') : null
            ),
            $this->url($action),
            esc_attr($method),
            $nonce,
            $fieldsHtml,
            $submitHtml
        );
    }
}
