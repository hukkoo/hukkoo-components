<?php

namespace Hukkoo\Components;

defined('ABSPATH') || exit;

/**
 * Named-component resolution. A host plugin extends or overrides the
 * default set via the hukkoo_components_register filter rather than
 * subclassing anything:
 *
 *     add_filter('hukkoo_components_register', function (array $components) {
 *         $components['stat-card'] = My\Product\StatCard::class;
 *         return $components;
 *     });
 */
final class ComponentRegistry
{
    private static ?array $components = null;

    public static function boot(): void
    {
        self::$components = apply_filters('hukkoo_components_register', self::defaults());
    }

    public static function make(string $name, array $args = []): Component
    {
        if (self::$components === null) {
            self::boot();
        }

        if (!isset(self::$components[$name])) {
            throw new \InvalidArgumentException("Unknown component: {$name}");
        }

        $class = self::$components[$name];

        return new $class($args);
    }

    public static function has(string $name): bool
    {
        if (self::$components === null) {
            self::boot();
        }

        return isset(self::$components[$name]);
    }

    public static function all(): array
    {
        if (self::$components === null) {
            self::boot();
        }

        return self::$components;
    }

    private static function defaults(): array
    {
        return [
            'badge'       => Components\Badge::class,
            'button'      => Components\Button::class,
            'card'        => Components\Card::class,
            'icon-button' => Components\IconButton::class,
            'modal'       => Components\Modal::class,
            'toast'       => Components\Toast::class,

            'crud-table' => Data\CrudTable::class,
            'list-table' => Data\ListTable::class,
            'pagination' => Data\Pagination::class,
            'table'      => Data\Table::class,

            'checkbox'    => Forms\Checkbox::class,
            'color'       => Forms\Color::class,
            'date'        => Forms\Date::class,
            'datetime'    => Forms\DateTime::class,
            'file-upload' => Forms\FileUpload::class,
            'form'        => Forms\Form::class,
            'lookup'      => Forms\Lookup::class,
            'number'      => Forms\Number::class,
            'radio'       => Forms\Radio::class,
            'select'      => Forms\Select::class,
            'text'        => Forms\Text::class,
            'textarea'    => Forms\Textarea::class,
            'time'        => Forms\Time::class,

            'container' => Layout\Container::class,
        ];
    }
}
