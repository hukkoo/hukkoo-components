<?php

namespace Hukkoo\Components\Data;

use Hukkoo\Components\Component;

defined('ABSPATH') || exit;

/**
 * $args:
 *   columns   array   [['key' => 'name', 'label' => 'Name', 'format' => ?callable], …]
 *   rows      array   List of assoc arrays keyed by column 'key'
 *   size      string  'sm'|'md'|'lg'  Row density (default: 'md')
 *   striped   bool    Alternating row background
 *   bordered  bool    Full cell grid instead of horizontal rules only
 *   body_id   string  id attribute on <tbody>, for a host's own JS to target directly (escaped)
 *   empty_message string  Shown when $rows is empty (escaped)
 *
 * Every cell is routed through Component::cell(), which escapes the value
 * whether or not a 'format' callback is set — a format callback's return
 * value is still escaped unless it explicitly returns Html::raw(). There
 * is no "no formatter means no escaping" path.
 */
final class Table extends Component
{
    public function render(): string
    {
        $columns  = $this->args['columns'] ?? [];
        $rows     = $this->args['rows'] ?? [];
        $size     = $this->args['size'] ?? 'md';
        $striped  = (bool) ($this->args['striped'] ?? false);
        $bordered = (bool) ($this->args['bordered'] ?? false);

        $class = $this->classes(
            $this->bem('table'),
            $size !== 'md' ? $this->bem('table', $size) : null,
            [
                'hk-table--striped'  => $striped,
                'hk-table--bordered' => $bordered,
            ]
        );

        $head    = implode('', array_map(
            fn (array $col) => sprintf('<th>%s</th>', $this->text($col['label'] ?? $col['key'] ?? '')),
            $columns
        ));
        $body_id = isset($this->args['body_id']) ? sprintf(' id="%s"', esc_attr($this->args['body_id'])) : '';

        if (empty($rows)) {
            $message = $this->args['empty_message'] ?? __('No records found.', 'hukkoo-components');

            return sprintf(
                '<div class="%s"><table class="%s"><thead><tr>%s</tr></thead><tbody%s><tr><td colspan="%d" class="%s">%s</td></tr></tbody></table></div>',
                $this->bem('table-wrap'),
                $class,
                $head,
                $body_id,
                max(1, count($columns)),
                $this->classes($this->bem('table-empty')),
                $this->text($message)
            );
        }

        $body = implode('', array_map(function (array $row) use ($columns) {
            $cells = implode('', array_map(function (array $col) use ($row) {
                $value = $row[$col['key']] ?? '';

                return sprintf('<td>%s</td>', $this->cell($value, $col['format'] ?? null));
            }, $columns));

            return sprintf('<tr>%s</tr>', $cells);
        }, $rows));

        return sprintf(
            '<div class="%s"><table class="%s"><thead><tr>%s</tr></thead><tbody%s>%s</tbody></table></div>',
            $this->bem('table-wrap'),
            $class,
            $head,
            $body_id,
            $body
        );
    }
}
