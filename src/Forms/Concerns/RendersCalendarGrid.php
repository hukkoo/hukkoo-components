<?php

namespace Hukkoo\Components\Forms\Concerns;

defined('ABSPATH') || exit;

/**
 * Shared by Date and DateTime — both need the identical month-grid
 * markup/math, just wrapped differently (Date alone vs. alongside a
 * time picker). Requires the using class to extend Component (for
 * bem()/classes()/text()).
 */
trait RendersCalendarGrid
{
    /** @return string[] */
    private function calendar_weekday_labels(): array
    {
        return ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    }

    private function render_calendar_grid(int $year, int $month, string $value, ?string $min, ?string $max): string
    {
        $first         = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $days_in_month = (int) $first->format('t');
        $start_weekday = (int) $first->format('w');
        $today         = (new \DateTimeImmutable('today'))->format('Y-m-d');

        $prev         = $first->modify('-1 day');
        $days_in_prev = (int) $prev->format('t');

        $cells = '';

        for ($i = $start_weekday - 1; $i >= 0; $i--) {
            $day    = $days_in_prev - $i;
            $cells .= $this->calendar_day_cell(sprintf('%s-%02d', $prev->format('Y-m'), $day), $day, true, $today, $value, $min, $max);
        }

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date   = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $cells .= $this->calendar_day_cell($date, $day, false, $today, $value, $min, $max);
        }

        $trailing = (7 - (($start_weekday + $days_in_month) % 7)) % 7;
        $next     = $first->modify('+1 month');

        for ($day = 1; $day <= $trailing; $day++) {
            $cells .= $this->calendar_day_cell(sprintf('%s-%02d', $next->format('Y-m'), $day), $day, true, $today, $value, $min, $max);
        }

        $weekday_html = '';
        foreach ($this->calendar_weekday_labels() as $label) {
            $weekday_html .= sprintf('<span class="%s">%s</span>', $this->bem('date-weekday'), esc_html($label));
        }

        return sprintf(
            '<div class="%s">'
                . '<button type="button" class="%s" data-hk-date-nav="prev" aria-label="%s">‹</button>'
                . '<span class="%s" data-hk-date-label>%s</span>'
                . '<button type="button" class="%s" data-hk-date-nav="next" aria-label="%s">›</button>'
            . '</div>'
            . '<div class="%s">%s</div>'
            . '<div class="%s" role="grid" data-hk-date-grid>%s</div>',
            $this->bem('date-header'),
            $this->bem('date-nav'),
            esc_attr__('Previous month', 'hukkoo-components'),
            $this->bem('date-label'),
            esc_html($first->format('F Y')),
            $this->bem('date-nav'),
            esc_attr__('Next month', 'hukkoo-components'),
            $this->bem('date-weekdays'),
            $weekday_html,
            $this->bem('date-grid'),
            $cells
        );
    }

    private function calendar_day_cell(string $date, int $day, bool $outside, string $today, string $value, ?string $min, ?string $max): string
    {
        $disabled = ($min !== null && $date < $min) || ($max !== null && $date > $max);

        $class = $this->classes(
            $this->bem('date-day'),
            [
                'hk-date-day--outside'  => $outside,
                'hk-date-day--today'    => $date === $today,
                'hk-date-day--selected' => $date === $value,
            ]
        );

        return sprintf(
            '<button type="button" class="%s" data-hk-date-day="%s"%s%s>%s</button>',
            $class,
            esc_attr($date),
            $disabled ? ' disabled' : '',
            $date === $value ? ' aria-selected="true"' : '',
            esc_html((string) $day)
        );
    }
}
