<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

/**
 * Renders a component's $args table straight from its class docblock via
 * Reflection, so the docs can't silently drift as far as hand-written
 * duplicate docs can — a renamed/removed arg with a stale docblock is a
 * visible mismatch in code review, not a separate doc site to remember to
 * update. Components take a single `array $args` constructor parameter,
 * so this reads the documented "$args:" block rather than typed
 * constructor parameters.
 */
final class ApiReference
{
    public static function fromReflection(string $component_class): string
    {
        try {
            $reflection = new \ReflectionClass($component_class);
        } catch (\ReflectionException) {
            return '';
        }

        $doc  = $reflection->getDocComment();
        $rows = $doc !== false ? self::parse_args_block($doc) : [];

        if (empty($rows)) {
            return sprintf(
                '<p class="hk-u-text-muted">%s</p>',
                esc_html(sprintf(
                    /* translators: %s: PHP class name */
                    __('No documented $args block found on %s.', 'hukkoo-components'),
                    $component_class
                ))
            );
        }

        $body = '';
        foreach ($rows as $row) {
            $body .= sprintf(
                '<tr><td><code>%s</code></td><td><code>%s</code></td><td>%s</td></tr>',
                esc_html($row['name']),
                esc_html($row['type']),
                esc_html($row['description'])
            );
        }

        return '<table class="hk-table hk-api-reference"><thead><tr>'
            . '<th>' . esc_html__('Arg', 'hukkoo-components') . '</th>'
            . '<th>' . esc_html__('Type', 'hukkoo-components') . '</th>'
            . '<th>' . esc_html__('Description', 'hukkoo-components') . '</th>'
            . '</tr></thead><tbody>' . $body . '</tbody></table>';
    }

    /** @return array<int, array{name: string, type: string, description: string}> */
    private static function parse_args_block(string $doc_comment): array
    {
        $lines       = preg_split('/\r\n|\r|\n/', $doc_comment) ?: [];
        $rows        = [];
        $in_block    = false;
        $base_indent = null;

        foreach ($lines as $line) {
            $line = preg_replace('/^\s*\/?\*+\/?\s?/', '', $line) ?? '';

            if (trim($line) === '$args:') {
                $in_block = true;
                continue;
            }

            if (!$in_block) {
                continue;
            }

            if (trim($line) === '') {
                break;
            }

            $indent = strlen($line) - strlen(ltrim($line));
            $base_indent ??= $indent;

            // A wrapped description continues on the next line indented
            // past the name/type columns rather than restarting them —
            // append it to the row already in progress instead of
            // misreading its first two words as a new arg's name/type.
            if ($indent > $base_indent && !empty($rows)) {
                $rows[count($rows) - 1]['description'] .= ' ' . trim($line);
                continue;
            }

            if (preg_match('/^\s*(\S+)\s+(\S+)\s+(.*)$/', $line, $m)) {
                $rows[] = [
                    'name'        => $m[1],
                    'type'        => $m[2],
                    'description' => trim($m[3]),
                ];
            }
        }

        return $rows;
    }
}
