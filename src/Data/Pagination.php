<?php

namespace Hukkoo\Components\Data;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * A "joined" prev/numbered/next button group. Purely presentational —
 * this library has no data layer to paginate against, so by default
 * every button just carries `data-hk-page="N"`; wiring the click to
 * actually change pages is the host's job (see the Table gallery's full
 * example for a worked client-side case). Truncates with an ellipsis
 * once there are more than a few pages either side of the current one.
 *
 * $args:
 *   current  int        Current page, 1-indexed (required)
 *   total    int        Total page count (required)
 *   url      ?callable  fn(int $page): string — when set, renders real
 *            <a href> navigation instead of data-hk-page buttons, for a
 *            server-driven host where each page is an actual page load
 *            (e.g. ListTable). The current page and disabled prev/next
 *            ends render as an inert <span> rather than a link.
 */
final class Pagination extends Component
{
    public function render(): string
    {
        $current = max(1, (int) ($this->args['current'] ?? 1));
        $total   = max(1, (int) ($this->args['total'] ?? 1));
        $url     = $this->args['url'] ?? null;

        $pages = [];
        for ($p = 1; $p <= $total; $p++) {
            if ($p === 1 || $p === $total || abs($p - $current) <= 1) {
                $pages[] = $p;
            } elseif (end($pages) !== '…') {
                $pages[] = '…';
            }
        }

        $buttons = $this->nav_button('«', max(1, $current - 1), $current === 1, $url);

        foreach ($pages as $page) {
            $buttons .= $page === '…'
                ? sprintf('<span class="%s">…</span>', $this->bem('pagination-ellipsis'))
                : $this->page_button((int) $page, $page === $current, $url);
        }

        $buttons .= $this->nav_button('»', min($total, $current + 1), $current === $total, $url);

        return sprintf(
            '<div class="%s" role="navigation" aria-label="%s">%s</div>',
            $this->bem('pagination'),
            esc_attr__('Pagination', 'hukkoo-components'),
            $buttons
        );
    }

    private function page_button(int $page, bool $active, ?callable $url): string
    {
        if ($url !== null) {
            return $active
                ? sprintf(
                    '<span class="%s" aria-current="page">%d</span>',
                    $this->classes($this->bem('pagination-item'), ['hk-pagination-item--active' => true]),
                    $page
                )
                : sprintf(
                    '<a href="%s" class="%s">%d</a>',
                    $this->url($url($page)),
                    $this->bem('pagination-item'),
                    $page
                );
        }

        return sprintf(
            '<button type="button" class="%s" data-hk-page="%d"%s>%d</button>',
            $this->classes($this->bem('pagination-item'), ['hk-pagination-item--active' => $active]),
            $page,
            $active ? ' aria-current="page"' : '',
            $page
        );
    }

    private function nav_button(string $label, int $target_page, bool $disabled, ?callable $url): string
    {
        if ($url !== null) {
            return $disabled
                ? sprintf(
                    '<span class="%s" aria-hidden="true">%s</span>',
                    $this->classes($this->bem('pagination-item'), ['hk-pagination-item--disabled' => true]),
                    esc_html($label)
                )
                : sprintf(
                    '<a href="%s" class="%s">%s</a>',
                    $this->url($url($target_page)),
                    $this->bem('pagination-item'),
                    esc_html($label)
                );
        }

        return sprintf(
            '<button type="button" class="%s" data-hk-page="%d"%s>%s</button>',
            $this->bem('pagination-item'),
            $target_page,
            $disabled ? ' disabled' : '',
            esc_html($label)
        );
    }
}
