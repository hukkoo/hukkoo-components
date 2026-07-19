<?php

namespace Hukkoo\Components\Forms\Concerns;

defined('ABSPATH') || exit;

/**
 * Shared by Time and DateTime — an hour column + a 5-minute-step minute
 * column, each a scrollable list of option buttons (same shape as
 * Select's listbox). 5-minute steps keep the list a manageable length;
 * a value outside that grid (e.g. one already stored from elsewhere)
 * still displays correctly, it just won't be reachable by clicking.
 */
trait RendersTimeColumns
{
    private function render_time_columns(string $hour, string $minute): string
    {
        $hour_html = '';
        for ($h = 0; $h < 24; $h++) {
            $hour_html .= $this->time_option(sprintf('%02d', $h), $hour);
        }

        $minute_html = '';
        for ($m = 0; $m < 60; $m += 5) {
            $minute_html .= $this->time_option(sprintf('%02d', $m), $minute);
        }

        return sprintf(
            '<div class="%s">'
                . '<div class="%s" data-hk-time-part="hour" role="listbox" aria-label="%s">%s</div>'
                . '<div class="%s" data-hk-time-part="minute" role="listbox" aria-label="%s">%s</div>'
            . '</div>',
            $this->bem('time-columns'),
            $this->bem('time-column'),
            esc_attr__('Hour', 'hukkoo-components'),
            $hour_html,
            $this->bem('time-column'),
            esc_attr__('Minute', 'hukkoo-components'),
            $minute_html
        );
    }

    private function time_option(string $option_value, string $selected_value): string
    {
        $is_selected = $option_value === $selected_value;

        return sprintf(
            '<button type="button" class="%s" role="option" tabindex="-1" data-hk-time-option="%s"%s>%s</button>',
            $this->classes($this->bem('time-option'), ['hk-time-option--selected' => $is_selected]),
            esc_attr($option_value),
            $is_selected ? ' aria-selected="true"' : '',
            esc_html($option_value)
        );
    }
}
