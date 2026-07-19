<?php

namespace Hukkoo\Components\Data;

use Hukkoo\Components\Component;
use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Forms\Select;
use Hukkoo\Components\Forms\Text;
use Hukkoo\Components\Html;

defined('ABSPATH') || exit;

/**
 * The server-driven counterpart to CrudTable: a searchable, sortable,
 * paginated, bulk-selectable records grid for a host that already has
 * its own query layer (real SQL search/sort/LIMIT-OFFSET) rather than an
 * in-memory list — every interaction here is a real navigation (a link
 * or a form GET), never client-side JS re-filtering/re-sorting rows
 * already on the page. Use CrudTable instead when the full dataset is
 * small enough to hand over at once and let the browser do the work.
 *
 * Composes Table, Text, Select, Button and Pagination (via Pagination's
 * `url` arg, which switches it from data-hk-page buttons to real <a>
 * navigation) rather than reimplementing any of them.
 *
 * $args:
 *   id                  string    Unique id prefix for this table's DOM ids (required)
 *   columns             array     [['key' => 'name', 'label' => 'Name', 'format' => ?callable, 'sortable' => bool], …]
 *   rows                array     List of assoc arrays keyed by column 'key'. A row needs a reserved '_id' key
 *                                 (the record id) if $bulk_actions, $edit_action or $delete_action is used.
 *   current_sort_key    string    Which column key the given $rows are already ordered by (for the header's arrow)
 *   current_sort_dir    string    'asc'|'desc' (default: 'asc')
 *   sort_url            callable  fn(string $column_key): string — href for clicking that column's header;
 *                                 the host decides the resulting sort direction (e.g. flip if $column_key already
 *                                 matches current_sort_key, else default to 'asc')
 *   search_value        string    Current search query, prefilled into the search box
 *   search_placeholder  string    (default: 'Search…')
 *   search_action       string    <form> action attribute for the search box (default: '', i.e. the current URL)
 *   hidden_fields        array    [name => value] hidden inputs carried on the search form, so submitting a
 *                                 search doesn't drop other query args the host's current URL depends on
 *                                 (e.g. ['page' => 'hukkoo-data-customer'])
 *   add_button          array     Button $args (label, url, …) shown in the toolbar — omit to hide
 *   toolbar_actions     Html      Extra trusted toolbar markup after $add_button (e.g. an "Export CSV" link)
 *   edit_action         callable  fn($row): ?array — Button $args (label, url) for that row, or null to hide it
 *   delete_action       callable  fn($row): ?array — ['label' => string, 'url' => string, 'confirm' => string]
 *                                 or null to hide. 'url' is a real link the host's own endpoint handles (this
 *                                 component has no data layer of its own); 'confirm' is shown via a plain JS
 *                                 confirm() before navigating, the same mechanism WP's own row actions use.
 *   bulk_actions        array     [value => label] listed in a "Bulk actions" select — omit/empty hides the
 *                                 checkbox column and the whole bulk-actions bar
 *   bulk_action_url     string    <form> action attribute for the bulk-actions bar (method="get")
 *   bulk_hidden_fields  array     [name => value] hidden inputs for that form — the host's own routing (e.g.
 *                                 which admin-post action handles it), slug, nonce, etc.
 *   pagination          array     ['current' => int, 'total_pages' => int, 'url' => callable fn(int $page): string]
 *   total_label         string    e.g. "24 items" shown beside pagination (escaped)
 *   empty_message       string    Shown when $rows is empty (escaped)
 */
final class ListTable extends Component
{
    public function render(): string
    {
        $id      = $this->args['id'] ?? 'hk-list-table';
        $columns = $this->args['columns'] ?? [];
        $rows    = $this->args['rows'] ?? [];

        $bulkActions = $this->args['bulk_actions'] ?? [];
        $hasBulk     = [] !== $bulkActions;
        $hasActions  = isset($this->args['edit_action']) || isset($this->args['delete_action']);

        $tableColumns = $hasBulk ? [$this->checkbox_column($id)] : [];

        foreach ($columns as $col) {
            $label = $col['label'] ?? $col['key'] ?? '';

            if (!empty($col['sortable']) && isset($this->args['sort_url'])) {
                $label = $this->sortable_header((string) $col['key'], (string) $label);
            }

            $tableColumns[] = [
                'key'    => $col['key'],
                'label'  => $label,
                'format' => $col['format'] ?? null,
            ];
        }

        if ($hasActions) {
            $tableColumns[] = [
                'key'    => '_actions',
                'label'  => __('Actions', 'hukkoo-components'),
                'format' => static fn (Html $html): Html => $html,
            ];
        }

        $tableRows = [];
        foreach ($rows as $row) {
            $tableRow = $row;

            if ($hasBulk) {
                $tableRow['_cb'] = $row['_id'] ?? null;
            }

            if ($hasActions) {
                $tableRow['_actions'] = $this->render_row_actions($row);
            }

            $tableRows[] = $tableRow;
        }

        $table = (new Table([
            'columns'       => $tableColumns,
            'rows'          => $tableRows,
            'body_id'       => $id . '-tbody',
            'empty_message' => $this->args['empty_message'] ?? __('No records found.', 'hukkoo-components'),
        ]))->render();

        $body = $this->render_toolbar($id)
            . ($hasBulk ? $this->render_bulk_form($bulkActions, $table) : $table)
            . $this->render_footer();

        return sprintf('<div class="hk-list-table" id="%s">%s</div>', esc_attr($id), $body);
    }

    private function checkbox_column(string $id): array
    {
        return [
            'key'    => '_cb',
            'label'  => Html::raw(sprintf(
                '<input type="checkbox" class="hk-checkbox" data-hk-check-all="%s" aria-label="%s">',
                esc_attr($id),
                esc_attr__('Select all', 'hukkoo-components')
            )),
            'format' => static fn (?int $rowId): Html => null === $rowId
                ? Html::raw('')
                : Html::raw(sprintf('<input type="checkbox" class="hk-checkbox" name="record[]" value="%d">', $rowId)),
        ];
    }

    private function sortable_header(string $key, string $label): Html
    {
        $currentKey = $this->args['current_sort_key'] ?? null;
        $currentDir = $this->args['current_sort_dir'] ?? 'asc';
        $isCurrent  = $currentKey === $key;
        $sortUrl    = $this->args['sort_url'];

        return Html::raw(sprintf(
            '<a class="hk-table-sort" href="%s">%s<span class="hk-sort-indicator">%s</span></a>',
            $this->url($sortUrl($key)),
            esc_html($label),
            $isCurrent ? ('asc' === $currentDir ? '▲' : '▼') : ''
        ));
    }

    private function render_row_actions(array $row): Html
    {
        $buttons = '';

        if (isset($this->args['edit_action'])) {
            $edit = ($this->args['edit_action'])($row);
            if ($edit !== null) {
                $buttons .= (new Button(array_merge(['style' => 'outline', 'size' => 'sm'], $edit)))->render();
            }
        }

        if (isset($this->args['delete_action'])) {
            $delete = ($this->args['delete_action'])($row);
            if ($delete !== null) {
                $attrs = isset($delete['confirm'])
                    ? ['onclick' => sprintf('return confirm(%s)', wp_json_encode((string) $delete['confirm']))]
                    : [];

                $buttons .= (new Button([
                    'label' => $delete['label'] ?? __('Delete', 'hukkoo-components'),
                    'url'   => $delete['url'],
                    'color' => 'error',
                    'style' => 'outline',
                    'size'  => 'sm',
                    'attrs' => $attrs,
                ]))->render();
            }
        }

        return Html::raw(sprintf('<div class="hk-table-actions">%s</div>', $buttons));
    }

    private function render_toolbar(string $id): string
    {
        $search = (new Text([
            'name'        => 's',
            'value'       => $this->args['search_value'] ?? '',
            'placeholder' => $this->args['search_placeholder'] ?? __('Search…', 'hukkoo-components'),
        ]))->render();

        $hidden = '';
        foreach ($this->args['hidden_fields'] ?? [] as $name => $value) {
            $hidden .= sprintf('<input type="hidden" name="%s" value="%s" />', esc_attr((string) $name), esc_attr((string) $value));
        }

        $searchForm = sprintf(
            '<form method="get" action="%s" class="hk-table-toolbar-search">%s%s</form>',
            esc_url($this->args['search_action'] ?? ''),
            $hidden,
            $search
        );

        $addButton = !empty($this->args['add_button'])
            ? (new Button($this->args['add_button']))->render()
            : '';

        $toolbarActions = isset($this->args['toolbar_actions']) ? (string) $this->args['toolbar_actions'] : '';

        return sprintf(
            '<div class="hk-table-toolbar">%s<div class="hk-table-toolbar-spacer"></div>%s%s</div>',
            $searchForm,
            $addButton,
            $toolbarActions
        );
    }

    private function render_bulk_form(array $bulkActions, string $table): string
    {
        $select = (new Select([
            'name'        => 'bulk_action',
            'placeholder' => __('Bulk actions', 'hukkoo-components'),
            'options'     => $bulkActions,
        ]))->render();

        $apply = (new Button([
            'label' => __('Apply', 'hukkoo-components'),
            'type'  => 'submit',
            'style' => 'outline',
        ]))->render();

        $hidden = '';
        foreach ($this->args['bulk_hidden_fields'] ?? [] as $name => $value) {
            $hidden .= sprintf('<input type="hidden" name="%s" value="%s" />', esc_attr((string) $name), esc_attr((string) $value));
        }

        return sprintf(
            '<form method="get" action="%s">%s<div class="hk-table-bulk-bar">%s%s</div>%s</form>',
            esc_url($this->args['bulk_action_url'] ?? ''),
            $hidden,
            $select,
            $apply,
            $table
        );
    }

    private function render_footer(): string
    {
        $pagination = $this->args['pagination'] ?? null;
        if (null === $pagination) {
            return '';
        }

        $paginationHtml = (new Pagination([
            'current' => $pagination['current'] ?? 1,
            'total'   => $pagination['total_pages'] ?? 1,
            'url'     => $pagination['url'] ?? null,
        ]))->render();

        $label = isset($this->args['total_label'])
            ? sprintf('<p class="hk-field-description hk-table-footer-label">%s</p>', esc_html($this->args['total_label']))
            : '';

        return sprintf('<div class="hk-table-footer">%s%s</div>', $label, $paginationHtml);
    }
}
