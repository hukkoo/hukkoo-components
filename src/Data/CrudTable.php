<?php

namespace Hukkoo\Components\Data;

use Hukkoo\Components\Component;
use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Components\Modal;
use Hukkoo\Components\Forms\Text;
use Hukkoo\Components\Html;

defined('ABSPATH') || exit;

/**
 * The "list of records with search, sortable columns, an Add button, and
 * per-row View/Edit/Delete actions" shape that comes up anywhere a host
 * shows a manageable list of things — composes Table, Text, Button, Modal
 * and Pagination rather than reimplementing any of them (this is the
 * pattern the Table gallery's own "Full example" demos hand-build; this
 * component is that pattern productized for real hosts to use directly).
 *
 * Search/sort/pagination all run client-side against whatever $rows was
 * already rendered — this has no data layer of its own, same as Table and
 * Pagination. A sortable column's raw value is what both sort and search
 * work against (via a data-* attribute on the row — see Table's own
 * '_attrs'), not a column's 'format'-ted display.
 *
 * $args:
 *   id                  string    Unique id prefix for this table's DOM ids and JS scoping (required)
 *   columns             array     [['key' => 'name', 'label' => 'Name', 'format' => ?callable, 'sortable' => bool], …]
 *   rows                array     List of assoc arrays keyed by column 'key'
 *   numbered            bool      Prefix each row with a running "Sl.No" column (default: false).
 *                                 Reflects each row's position in the currently visible (searched/
 *                                 sorted/paginated) list, not a fixed per-row id — the JS renumbers
 *                                 it on every re-render, same as the range label.
 *   search_placeholder  string    (default: 'Search…')
 *   page_size           int       Rows per page, client-side (default: 10)
 *   add_button          array     Button $args (label, url, …) shown in the toolbar — omit to hide
 *   empty_message       string    Shown when $rows is empty (escaped)
 *   view_action         callable  fn($row): ?array — Button $args (label, url) for that row, or null to hide it for that row
 *   edit_action         callable  fn($row): ?array — same shape as view_action
 *   delete_action       callable  fn($row): ?array — ['title' => string, 'message' => string, 'form' => Html] or null to hide.
 *                                 'form' is the delete <form>'s inner HTML (hidden fields + nonce) — the actual
 *                                 delete mechanism (POST action, field names, nonce action) is host-specific,
 *                                 same reasoning as Pagination leaving page-change wiring to the host.
 */
final class CrudTable extends Component
{
    public function render(): string
    {
        $id       = $this->args['id'] ?? 'hk-crud-table';
        $columns  = $this->args['columns'] ?? [];
        $rows     = $this->args['rows'] ?? [];
        $pageSize = max(1, (int) ($this->args['page_size'] ?? 10));

        [$tableColumns, $sortableKeys] = $this->buildColumns($columns);

        $numbered = (bool) ($this->args['numbered'] ?? false);
        if ($numbered) {
            array_unshift($tableColumns, [
                'key'    => '_sl_no',
                'label'  => __('Sl.No', 'hukkoo-components'),
                'format' => static fn (int $value): Html => Html::raw(sprintf('<span data-hk-slno>%d</span>', $value)),
            ]);
        }

        $hasActions = isset($this->args['view_action']) || isset($this->args['edit_action']) || isset($this->args['delete_action']);
        if ($hasActions) {
            $tableColumns[] = [
                'key'    => '_actions',
                'label'  => __('Actions', 'hukkoo-components'),
                'format' => static fn (Html $html): Html => $html,
            ];
        }

        $modals    = '';
        $tableRows = [];
        foreach ($rows as $index => $row) {
            $tableRow = $row;

            if ($numbered) {
                $tableRow['_sl_no'] = (int) $index + 1;
            }

            $attrs = [];
            foreach ($sortableKeys as $key) {
                $attrs['data-' . str_replace('_', '-', $key)] = (string) ($row[$key] ?? '');
            }
            $tableRow['_attrs'] = $attrs;

            if ($hasActions) {
                $tableRow['_actions'] = $this->render_row_actions($id, (int) $index, $row, $modals);
            }

            $tableRows[] = $tableRow;
        }

        $table = (new Table([
            'columns'       => $tableColumns,
            'rows'          => $tableRows,
            'body_id'       => $id . '-tbody',
            'empty_message' => $this->args['empty_message'] ?? __('No records found.', 'hukkoo-components'),
        ]))->render();

        $search = (new Text([
            'name'        => $id . '-search',
            'placeholder' => $this->args['search_placeholder'] ?? __('Search…', 'hukkoo-components'),
        ]))->render();

        $addButton = !empty($this->args['add_button'])
            ? (new Button($this->args['add_button']))->render()
            : '';

        $total       = count($rows);
        $totalPages  = max(1, (int) ceil($total / $pageSize));
        $pagination  = (new Pagination(['current' => 1, 'total' => $totalPages]))->render();
        $range_label = $total === 0
            ? ''
            : sprintf(
                /* translators: 1: first row number, 2: last row number, 3: total row count */
                __('Showing %1$d–%2$d of %3$d', 'hukkoo-components'),
                1,
                min($total, $pageSize),
                $total
            );

        $body = sprintf(
            '<div class="hk-table-toolbar">'
                . '<div class="hk-table-toolbar-search">%s</div>'
                . '<div class="hk-table-toolbar-spacer"></div>'
                . '%s'
            . '</div>'
            . '%s'
            . '<div class="hk-table-footer">'
                . '<p class="hk-field-description hk-table-footer-label" id="%s-range">%s</p>'
                . '<div id="%s-pagination">%s</div>'
            . '</div>'
            . '%s'
            . '%s',
            $search,
            $addButton,
            $table,
            esc_attr($id),
            esc_html($range_label),
            esc_attr($id),
            $pagination,
            $modals,
            $this->script($id, $pageSize, $sortableKeys)
        );

        return sprintf('<div class="hk-crud-table" id="%s">%s</div>', esc_attr($id), $body);
    }

    /** @return array{0: array, 1: array<int, string>} [$table_columns, $sortable_keys] */
    private function buildColumns(array $columns): array
    {
        $tableColumns = [];
        $sortableKeys = [];

        foreach ($columns as $col) {
            $label = $col['label'] ?? $col['key'] ?? '';

            if (!empty($col['sortable'])) {
                $sortableKeys[] = $col['key'];
                $label          = $this->sortable_header((string) $col['key'], (string) $label);
            }

            $tableColumns[] = [
                'key'    => $col['key'],
                'label'  => $label,
                'format' => $col['format'] ?? null,
            ];
        }

        return [$tableColumns, $sortableKeys];
    }

    private function sortable_header(string $key, string $label): Html
    {
        return Html::raw(sprintf(
            '<button type="button" class="hk-table-sort" data-hk-sort-key="%s">%s<span class="hk-sort-indicator" data-hk-sort-indicator="%s"></span></button>',
            esc_attr($key),
            esc_html($label),
            esc_attr($key)
        ));
    }

    private function render_row_actions(string $id, int $index, array $row, string &$modals): Html
    {
        $buttons = '';

        if (isset($this->args['view_action'])) {
            $view = ($this->args['view_action'])($row);
            if ($view !== null) {
                $buttons .= (new Button(array_merge(['style' => 'outline', 'size' => 'sm'], $view)))->render();
            }
        }

        if (isset($this->args['edit_action'])) {
            $edit = ($this->args['edit_action'])($row);
            if ($edit !== null) {
                $buttons .= (new Button(array_merge(['style' => 'outline', 'size' => 'sm'], $edit)))->render();
            }
        }

        if (isset($this->args['delete_action'])) {
            $delete = ($this->args['delete_action'])($row);
            if ($delete !== null) {
                $modalId  = sprintf('%s-delete-%d', $id, $index);
                $buttons .= (new Button([
                    'label' => __('Delete', 'hukkoo-components'),
                    'color' => 'error',
                    'size'  => 'sm',
                    'attrs' => ['data-hk-modal-open' => $modalId],
                ]))->render();

                $modals .= (new Modal([
                    'id'      => $modalId,
                    'size'    => 'sm',
                    'title'   => $delete['title'],
                    'content' => $delete['message'],
                    'actions' => Html::raw(
                        (new Button([
                            'label' => __('Cancel', 'hukkoo-components'),
                            'style' => 'ghost',
                            'attrs' => ['data-hk-modal-close' => true],
                        ]))->render()
                        . sprintf(
                            '<form method="post" style="display:inline;">%s%s</form>',
                            (string) $delete['form'],
                            (new Button(['label' => __('Delete', 'hukkoo-components'), 'color' => 'error', 'type' => 'submit']))->render()
                        )
                    ),
                ]))->render();
            }
        }

        return Html::raw(sprintf('<div class="hk-table-actions">%s</div>', $buttons));
    }

    /** @param array<int, string> $sortableKeys */
    private function script(string $id, int $pageSize, array $sortableKeys): string
    {
        $id_js       = wp_json_encode($id);
        $page_size   = (int) $pageSize;
        $search_keys = wp_json_encode(array_map(
            static fn (string $key): string => 'data-' . str_replace('_', '-', $key),
            $sortableKeys
        ));
        $showing_tpl = wp_json_encode(__('Showing %1$d–%2$d of %3$d', 'hukkoo-components'));
        $no_match    = wp_json_encode(__('No records match your search.', 'hukkoo-components'));

        return <<<HTML
        <script>
        (function () {
            var id = {$id_js};
            var tbody = document.getElementById(id + '-tbody');
            var searchInput = document.getElementById(id + '-search');
            var rangeLabel = document.getElementById(id + '-range');
            var paginationEl = document.getElementById(id + '-pagination');
            if (!tbody || !searchInput) {
                return;
            }

            var pageSize = {$page_size};
            var searchAttrs = {$search_keys};
            var currentPage = 1;
            var sortKey = null;
            var sortDir = 1;
            var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

            var i18n = { showing: {$showing_tpl}, noMatch: {$no_match} };

            function filtered() {
                var query = (searchInput.value || '').trim().toLowerCase();
                if (!query) {
                    return allRows.slice();
                }
                return allRows.filter(function (row) {
                    return searchAttrs.some(function (attr) {
                        return (row.getAttribute(attr) || '').toLowerCase().indexOf(query) !== -1;
                    });
                });
            }

            function sorted(rows) {
                if (!sortKey) {
                    return rows;
                }
                var attr = 'data-' + sortKey.replace(/_/g, '-');
                return rows.slice().sort(function (a, b) {
                    var av = a.getAttribute(attr) || '';
                    var bv = b.getAttribute(attr) || '';
                    var an = parseFloat(av);
                    var bn = parseFloat(bv);
                    if (!isNaN(an) && !isNaN(bn) && String(an) === av && String(bn) === bv) {
                        return (an - bn) * sortDir;
                    }
                    return av.localeCompare(bv) * sortDir;
                });
            }

            function paginationHtml(current, total) {
                function item(label, target, disabled, active) {
                    return '<button type="button" class="hk-pagination-item' + (active ? ' hk-pagination-item--active' : '') +
                        '" data-hk-page="' + target + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
                }
                var html = '<div class="hk-pagination">' + item('«', Math.max(1, current - 1), current === 1, false);
                var lastWasEllipsis = false;
                for (var p = 1; p <= total; p++) {
                    if (p === 1 || p === total || Math.abs(p - current) <= 1) {
                        html += item(p, p, false, p === current);
                        lastWasEllipsis = false;
                    } else if (!lastWasEllipsis) {
                        html += '<span class="hk-pagination-ellipsis">…</span>';
                        lastWasEllipsis = true;
                    }
                }
                html += item('»', Math.min(total, current + 1), current === total, false) + '</div>';
                return html;
            }

            function updateSortIndicators() {
                var table = tbody.closest('table');
                if (!table) {
                    return;
                }
                table.querySelectorAll('[data-hk-sort-indicator]').forEach(function (el) {
                    var key = el.getAttribute('data-hk-sort-indicator');
                    el.textContent = key === sortKey ? (sortDir === 1 ? '▲' : '▼') : '';
                });
            }

            function render() {
                var rows = sorted(filtered());
                var total = rows.length;
                var totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                allRows.forEach(function (row) {
                    row.hidden = true;
                });

                var start = (currentPage - 1) * pageSize;
                var pageRows = rows.slice(start, start + pageSize);
                pageRows.forEach(function (row, i) {
                    row.hidden = false;
                    tbody.appendChild(row);
                    var slNo = row.querySelector('[data-hk-slno]');
                    if (slNo) {
                        slNo.textContent = start + i + 1;
                    }
                });

                if (rangeLabel) {
                    rangeLabel.textContent = total === 0
                        ? i18n.noMatch
                        : i18n.showing
                            .replace('%1\$d', String(start + 1))
                            .replace('%2\$d', String(start + pageRows.length))
                            .replace('%3\$d', String(total));
                }

                if (paginationEl) {
                    paginationEl.innerHTML = paginationHtml(currentPage, totalPages);
                }

                updateSortIndicators();
            }

            searchInput.addEventListener('input', function () {
                currentPage = 1;
                render();
            });

            var table = tbody.closest('table');
            if (table) {
                table.querySelectorAll('[data-hk-sort-key]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var key = btn.getAttribute('data-hk-sort-key');
                        if (sortKey === key) {
                            sortDir *= -1;
                        } else {
                            sortKey = key;
                            sortDir = 1;
                        }
                        currentPage = 1;
                        render();
                    });
                });
            }

            if (paginationEl) {
                paginationEl.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-hk-page]');
                    if (!btn || btn.disabled) {
                        return;
                    }
                    currentPage = parseInt(btn.getAttribute('data-hk-page'), 10) || 1;
                    render();
                });
            }

            render();
        })();
        </script>
        HTML;
    }
}
