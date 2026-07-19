<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

/**
 * Minimal regex tokenizer for the PHP snippets galleries generate —
 * `(new X(['key' => 'value']))->render();` shapes, not general PHP source.
 * Not a real parser: good enough to color strings/keywords/class names/
 * arrows in that specific shape, nothing more.
 */
final class CodeHighlighter
{
    private const TOKEN_PATTERN = "/('(?:\\\\.|[^'\\\\])*'|\\b(?:new|array|true|false|null)\\b|\\b[A-Z][A-Za-z0-9_]*\\b|\\b[a-z_][A-Za-z0-9_]*\\b(?=\\()|->|=>)/";

    public static function render(string $code): string
    {
        $pieces = preg_split(self::TOKEN_PATTERN, $code, -1, PREG_SPLIT_DELIM_CAPTURE);
        $pieces = $pieces !== false ? $pieces : [$code];

        $html = '';
        foreach ($pieces as $i => $piece) {
            if ($piece === '') {
                continue;
            }

            // preg_split with PREG_SPLIT_DELIM_CAPTURE alternates plain
            // text (even index) and the captured token (odd index).
            $html .= $i % 2 === 1
                ? sprintf('<span class="%s">%s</span>', self::token_class($piece), esc_html($piece))
                : esc_html($piece);
        }

        return $html;
    }

    private static function token_class(string $token): string
    {
        return match (true) {
            $token[0] === "'" => 'hk-code-token--string',
            in_array($token, ['new', 'array', 'true', 'false', 'null'], true) => 'hk-code-token--keyword',
            $token === '->' || $token === '=>' => 'hk-code-token--arrow',
            ctype_upper($token[0]) => 'hk-code-token--class',
            default => 'hk-code-token--call',
        };
    }
}
