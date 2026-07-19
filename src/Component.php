<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Base class for every component. Centralizes class-list building,
 * attribute rendering, and output escaping in one place so escaping is
 * the default behavior rather than something each subclass has to
 * remember. See Html for the explicit escape hatch.
 *
 * Escaping convention: leaf-level values that can carry request/DB data
 * (labels, cell values, form values) must be routed through text()/url()/
 * cell(). Structural "content"/"children" args that hold already-composed
 * component HTML are treated as trusted, the same as a plain PHP template
 * would — re-escaping them would break composition (nested components'
 * markup would render as literal text). If a leaf value needs to carry
 * intentional HTML, wrap it in Html::raw() at the call site rather than
 * bypassing text() silently.
 */
abstract class Component
{
    protected array $args = [];

    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    abstract public function render(): string;

    public function __toString(): string
    {
        return $this->render();
    }

    protected function prefix(): string
    {
        return Library::prefix();
    }

    /**
     * BEM-ish class name: $this->bem('button', 'primary') => 'hk-button--primary'
     * $this->bem('button') => 'hk-button'
     */
    protected function bem(string $block, ?string $modifier = null): string
    {
        $name = $this->prefix() . '-' . $block;

        return $modifier ? $name . '--' . $modifier : $name;
    }

    /**
     * Builds a space-joined, esc_attr()-safe class string.
     *
     * Accepts plain strings and ['class-name' => bool] conditional maps,
     * e.g. $this->classes('hk-button', ['hk-button--block' => $full_width]).
     */
    protected function classes(...$classes): string
    {
        $flat = [];

        foreach ($classes as $entry) {
            if (is_array($entry)) {
                foreach ($entry as $class_name => $condition) {
                    if (is_int($class_name)) {
                        if ($condition) {
                            $flat[] = (string) $condition;
                        }
                        continue;
                    }
                    if ($condition) {
                        $flat[] = $class_name;
                    }
                }
                continue;
            }

            if ($entry) {
                $flat[] = (string) $entry;
            }
        }

        return esc_attr(implode(' ', array_filter($flat, static fn ($c) => $c !== '')));
    }

    /**
     * Builds an esc_attr()-safe HTML attribute string from a name => value
     * map. Boolean true renders a bare attribute (e.g. 'disabled'), null
     * or false omits it entirely.
     */
    protected function attributes(array $attrs): string
    {
        $pairs = [];

        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            $name = preg_replace('/[^a-zA-Z0-9_:.-]/', '', (string) $name);

            if ($name === '') {
                continue;
            }

            if ($value === true) {
                $pairs[] = $name;
                continue;
            }

            $pairs[] = sprintf('%s="%s"', $name, esc_attr((string) $value));
        }

        return implode(' ', $pairs);
    }

    /**
     * Escapes $value for HTML text content unless it's an explicit
     * Html::raw() fragment. Use this instead of echoing values directly.
     */
    protected function text(mixed $value): string
    {
        if ($value instanceof Html) {
            return (string) $value;
        }

        return esc_html((string) $value);
    }

    /**
     * Escapes $value for use as a URL attribute unless it's an explicit
     * Html::raw() fragment.
     */
    protected function url(mixed $value): string
    {
        if ($value instanceof Html) {
            return (string) $value;
        }

        return esc_url((string) $value);
    }

    /**
     * Renders a data cell value, optionally through a formatter. The
     * formatter's return value is still escaped unless it returns
     * Html::raw() — there is no "no formatter means no escaping" path.
     */
    protected function cell(mixed $value, ?callable $format = null): string
    {
        $formatted = $format ? $format($value) : $value;

        return $this->text($formatted);
    }
}
