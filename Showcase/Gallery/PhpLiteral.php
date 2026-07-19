<?php

namespace Hukkoo\Components\Showcase\Gallery;

defined('ABSPATH') || exit;

/**
 * Single-line PHP array literal formatter for gallery code snippets —
 * var_export()'s multi-line `array (\n  ...\n)` output doesn't fit the
 * compact one-liners these examples want to show.
 */
final class PhpLiteral
{
    public static function array_literal(array $args): string
    {
        $pairs = [];

        foreach ($args as $key => $value) {
            $pairs[] = sprintf('%s => %s', self::scalar($key), self::scalar($value));
        }

        return '[' . implode(', ', $pairs) . ']';
    }

    private static function scalar(mixed $value): string
    {
        return match (true) {
            is_array($value) => self::array_literal($value),
            is_string($value) => "'" . addcslashes($value, "'\\") . "'",
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            default => (string) $value,
        };
    }
}
