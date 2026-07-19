<?php

namespace Hukkoo\Components\Forms;

defined('ABSPATH') || exit;

/**
 * A Select variant for referencing another entity's records — same
 * button+listbox widget (see Select), plus a live search input at the
 * top of the panel since a lookup's option list is typically much
 * longer than a plain Select's. This library has no data layer of its
 * own, so `options` is still a plain [value => label] array the host
 * supplies up front; only the panel gets richer (filter-as-you-type),
 * not the data source itself — wiring that list to an async/paginated
 * source is the host's job.
 *
 * $args:
 *   name                string  Field name/id (required)
 *   label               string  (escaped)
 *   value               string  Selected option value (escaped)
 *   options             array   [value => label] pairs rendered as listbox options (escaped)
 *   placeholder         string  Shown when nothing is selected (escaped)
 *   search_placeholder  string  Search input placeholder (escaped, default: 'Search…')
 *   color               string  'neutral'|'primary'|'secondary'|'accent'|'info'|'success'|'warning'|'error'  Border/focus accent (default: none)
 *   size                string  'xs'|'sm'|'md'|'lg'|'xl'  (default: 'md')
 *   ghost               bool    Transparent, borderless until focused
 *   required            bool    Shows the required asterisk on the label
 *   disabled            bool    Disables the field
 *   error               string  Validation message (escaped)
 *   description         string  Help text (escaped)
 */
final class Lookup extends Field
{
    protected function render_input(): string
    {
        $color              = $this->args['color'] ?? null;
        $size               = $this->args['size'] ?? 'md';
        $ghost              = (bool) ($this->args['ghost'] ?? false);
        $name               = $this->args['name'] ?? '';
        $value              = $this->args['value'] ?? '';
        $options            = $this->args['options'] ?? [];
        $placeholder        = $this->args['placeholder'] ?? '';
        $search_placeholder = $this->args['search_placeholder'] ?? __('Search…', 'hukkoo-components');
        $disabled           = (bool) ($this->args['disabled'] ?? false);

        $selected_label = $placeholder;
        foreach ($options as $option_value => $option_label) {
            if ((string) $option_value === (string) $value) {
                $selected_label = $option_label;
                break;
            }
        }

        // Color lives on the wrapper (not the trigger) so the --hk-field-c
        // custom property it sets also cascades to the panel, which is
        // the trigger's sibling rather than its descendant.
        $wrapper_class = $this->classes(
            $this->bem('select'),
            $color !== null ? $this->bem('field-input', $color) : null
        );

        $trigger_attrs = [
            'type'                    => 'button',
            'id'                      => $name,
            'class'                   => $this->classes(
                $this->bem('field-input'),
                $this->bem('select-trigger'),
                $size !== 'md' ? $this->bem('field-input', $size) : null,
                $ghost ? $this->bem('field-input', 'ghost') : null
            ),
            'aria-haspopup'           => 'listbox',
            'aria-expanded'           => 'false',
            'disabled'                => $disabled,
            'data-hk-dropdown-toggle' => true,
        ];

        $search_attrs = [
            'type'                  => 'text',
            'class'                 => $this->bem('lookup-search'),
            'placeholder'           => $search_placeholder,
            'autocomplete'          => 'off',
            'data-hk-lookup-search' => true,
        ];

        $options_html = '';
        foreach ($options as $option_value => $option_label) {
            $is_selected = (string) $option_value === (string) $value;

            $options_html .= sprintf(
                '<li class="%s" role="option" tabindex="-1" aria-selected="%s" data-hk-select-option="%s" data-hk-lookup-text="%s">%s</li>',
                $this->classes(
                    $this->bem('select-option'),
                    ['hk-select-option--selected' => $is_selected]
                ),
                $is_selected ? 'true' : 'false',
                esc_attr((string) $option_value),
                esc_attr(function_exists('mb_strtolower') ? mb_strtolower((string) $option_label) : strtolower((string) $option_label)),
                $this->text($option_label)
            );
        }

        $hidden_attrs = [
            'type'                       => 'hidden',
            'name'                       => $name,
            'value'                      => $value,
            'data-hk-select-value-input' => true,
        ];

        return sprintf(
            '<div class="%s" data-hk-dropdown>'
                . '<button %s><span class="%s">%s</span><span class="%s" aria-hidden="true"></span></button>'
                . '<input %s />'
                . '<div class="%s" data-hk-dropdown-menu data-hk-lookup-panel hidden>'
                    . '<input %s />'
                    . '<ul class="%s" role="listbox">%s</ul>'
                    . '<p class="%s" data-hk-lookup-empty hidden>%s</p>'
                . '</div>'
            . '</div>',
            $wrapper_class,
            $this->attributes($trigger_attrs),
            $this->bem('select-value'),
            $this->text($selected_label),
            $this->bem('select-caret'),
            $this->attributes($hidden_attrs),
            $this->bem('lookup-panel'),
            $this->attributes($search_attrs),
            $this->bem('lookup-options'),
            $options_html,
            $this->bem('lookup-empty'),
            esc_html__('No matches', 'hukkoo-components')
        );
    }
}
