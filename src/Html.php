<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Marker for a string that is already safe to output as HTML.
 *
 * Component::text()/cell() escape everything by default; the only way to
 * skip that is to wrap the value in Html::raw() explicitly. That makes an
 * unescaped-output decision a visible, greppable call site instead of a
 * silently missing esc_html().
 */
final class Html implements \Stringable
{
    private function __construct(private readonly string $value)
    {
    }

    public static function raw(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
