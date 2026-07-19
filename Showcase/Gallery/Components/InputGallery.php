<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Forms\Checkbox;
use Hukkoo\Components\Forms\Color;
use Hukkoo\Components\Forms\Date;
use Hukkoo\Components\Forms\DateTime;
use Hukkoo\Components\Forms\FileUpload;
use Hukkoo\Components\Forms\Lookup;
use Hukkoo\Components\Forms\Number;
use Hukkoo\Components\Forms\Radio;
use Hukkoo\Components\Forms\Select;
use Hukkoo\Components\Forms\Text;
use Hukkoo\Components\Forms\Textarea;
use Hukkoo\Components\Forms\Time;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

/**
 * Every form-field component lives on this one page rather than one tab
 * per type — deliberately consolidated per user request rather than
 * split the way Button/Input started out.
 */
final class InputGallery implements GalleryInterface
{
    private const SELECT_OPTIONS = ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'];

    public static function slug(): string
    {
        return 'input';
    }

    public static function label(): string
    {
        return __('Input', 'hukkoo-components');
    }

    public static function render(): string
    {
        $classes = [
            'Text'     => Text::class,
            'Textarea' => Textarea::class,
            'Number'   => Number::class,
            'Select'   => Select::class,
            'Lookup'   => Lookup::class,
            'Date'     => Date::class,
            'DateTime' => DateTime::class,
            'Time'     => Time::class,
            'Color'    => Color::class,
            'File'     => FileUpload::class,
            'Checkbox' => Checkbox::class,
            'Radio'    => Radio::class,
        ];

        $api = '';
        foreach ($classes as $label => $class) {
            $api .= sprintf('<h4>%s</h4>%s', esc_html($label), ApiReference::fromReflection($class));
        }

        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Colors', 'hukkoo-components'), self::text_colors()),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::text_sizes()),
                GallerySection::render(__('Types', 'hukkoo-components'), self::text_types()),
                GallerySection::render(__('States', 'hukkoo-components'), self::text_states()),
                GallerySection::render(__('Textarea', 'hukkoo-components'), self::textarea_examples()),
                GallerySection::render(__('Number', 'hukkoo-components'), self::number_examples()),
                GallerySection::render(__('Select — Colors', 'hukkoo-components'), self::select_colors()),
                GallerySection::render(__('Select — Sizes', 'hukkoo-components'), self::select_sizes()),
                GallerySection::render(__('Select — States', 'hukkoo-components'), self::select_states()),
                GallerySection::render(__('Lookup', 'hukkoo-components'), self::lookup_examples()),
                GallerySection::render(__('Date', 'hukkoo-components'), self::date_examples()),
                GallerySection::render(__('Date/Time', 'hukkoo-components'), self::datetime_examples()),
                GallerySection::render(__('Time', 'hukkoo-components'), self::time_examples()),
                GallerySection::render(__('Color', 'hukkoo-components'), self::color_examples()),
                GallerySection::render(__('File', 'hukkoo-components'), self::file_examples()),
                GallerySection::render(__('Checkbox', 'hukkoo-components'), self::checkbox_examples()),
                GallerySection::render(__('Radio', 'hukkoo-components'), self::radio_examples()),
            ],
            $api
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function text_colors(): array
    {
        $colors   = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];
        $examples = [self::example(Text::class, 'Text', 'Default', ['name' => 'default', 'placeholder' => 'Default'])];

        foreach ($colors as $color) {
            $examples[] = self::example(Text::class, 'Text', ucfirst($color), [
                'name'        => $color,
                'placeholder' => ucfirst($color),
                'color'       => $color,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function text_sizes(): array
    {
        $sizes    = ['xs', 'sm', 'md', 'lg', 'xl'];
        $examples = [];

        foreach ($sizes as $size) {
            $examples[] = self::example(Text::class, 'Text', strtoupper($size), [
                'name'        => $size,
                'placeholder' => strtoupper($size),
                'color'       => 'primary',
                'size'        => $size,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function text_types(): array
    {
        $types = [
            'text'     => ['label' => 'Full name', 'placeholder' => 'Jane Doe'],
            'email'    => ['label' => 'Email address', 'placeholder' => 'jane@example.com'],
            'password' => ['label' => 'Password', 'placeholder' => ''],
        ];

        $examples = [];
        foreach ($types as $type => $args) {
            $examples[] = self::example(Text::class, 'Text', ucfirst($type), [
                'name'        => $type,
                'label'       => $args['label'],
                'type'        => $type,
                'placeholder' => $args['placeholder'],
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function text_states(): array
    {
        $states = [
            'Required' => [
                'name'     => 'username',
                'label'    => 'Username',
                'required' => true,
            ],
            'With description' => [
                'name'        => 'api_key',
                'label'       => 'API key',
                'description' => 'Found under Settings → Developer.',
            ],
            'With error' => [
                'name'  => 'phone',
                'label' => 'Phone number',
                'value' => '555-not-a-number',
                'error' => 'Enter a valid phone number.',
            ],
            'Ghost' => [
                'name'        => 'search',
                'placeholder' => 'Search…',
                'ghost'       => true,
            ],
            'Disabled' => [
                'name'        => 'disabled',
                'placeholder' => 'Disabled',
                'disabled'    => true,
            ],
        ];

        $examples = [];
        foreach ($states as $title => $args) {
            $examples[] = self::example(Text::class, 'Text', $title, $args);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function textarea_examples(): array
    {
        return [
            self::example(Textarea::class, 'Textarea', 'Default', [
                'name'        => 'note',
                'label'       => 'Note',
                'placeholder' => 'Write a note…',
            ]),
            self::example(Textarea::class, 'Textarea', 'With value', [
                'name'  => 'bio',
                'label' => 'Bio',
                'value' => "Runs the design system team.\nBased in Chennai.",
                'rows'  => 3,
            ]),
            self::example(Textarea::class, 'Textarea', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function number_examples(): array
    {
        return [
            self::example(Number::class, 'Number', 'Default', [
                'name'        => 'quantity',
                'label'       => 'Quantity',
                'placeholder' => '0',
            ]),
            self::example(Number::class, 'Number', 'With range', [
                'name'  => 'rating',
                'label' => 'Rating',
                'min'   => 1,
                'max'   => 5,
                'step'  => 1,
                'value' => 3,
            ]),
            self::example(Number::class, 'Number', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function select_colors(): array
    {
        $colors   = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];
        $examples = [self::example(Select::class, 'Select', 'Default', [
            'name'    => 'default',
            'value'   => 'md',
            'options' => self::SELECT_OPTIONS,
        ])];

        foreach ($colors as $color) {
            $examples[] = self::example(Select::class, 'Select', ucfirst($color), [
                'name'    => $color,
                'value'   => 'md',
                'color'   => $color,
                'options' => self::SELECT_OPTIONS,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function select_sizes(): array
    {
        $sizes    = ['xs', 'sm', 'md', 'lg', 'xl'];
        $examples = [];

        foreach ($sizes as $size) {
            $examples[] = self::example(Select::class, 'Select', strtoupper($size), [
                'name'    => $size,
                'value'   => 'md',
                'color'   => 'primary',
                'size'    => $size,
                'options' => self::SELECT_OPTIONS,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function select_states(): array
    {
        $states = [
            'Placeholder' => [
                'name'        => 'role',
                'placeholder' => 'Choose a role…',
                'options'     => ['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer'],
            ],
            'Required' => [
                'name'     => 'plan',
                'label'    => 'Plan',
                'required' => true,
                'options'  => ['free' => 'Free', 'pro' => 'Pro'],
            ],
            'With error' => [
                'name'    => 'country',
                'label'   => 'Country',
                'error'   => 'Select a country.',
                'options' => ['us' => 'United States', 'in' => 'India'],
            ],
            'Ghost' => [
                'name'    => 'sort',
                'value'   => 'newest',
                'ghost'   => true,
                'options' => ['newest' => 'Newest', 'oldest' => 'Oldest'],
            ],
            'Disabled' => [
                'name'     => 'select_disabled',
                'value'    => 'md',
                'disabled' => true,
                'options'  => self::SELECT_OPTIONS,
            ],
        ];

        $examples = [];
        foreach ($states as $title => $args) {
            $examples[] = self::example(Select::class, 'Select', $title, $args);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function lookup_examples(): array
    {
        $customers = [
            'cust_101' => 'Acme Corp',
            'cust_102' => 'Bluebird Studios',
            'cust_103' => 'Crescent Logistics',
            'cust_104' => 'Dockside Analytics',
            'cust_105' => 'Everline Retail',
            'cust_106' => 'Fenwick & Co',
        ];

        return [
            self::example(Lookup::class, 'Lookup', 'Default', [
                'name'    => 'customer',
                'label'   => 'Customer',
                'options' => $customers,
            ]),
            self::example(Lookup::class, 'Lookup', 'With value', [
                'name'    => 'customer_selected',
                'label'   => 'Customer',
                'value'   => 'cust_103',
                'color'   => 'primary',
                'options' => $customers,
            ]),
            self::example(Lookup::class, 'Lookup', 'Disabled', [
                'name'     => 'lookup_disabled',
                'label'    => 'Customer',
                'disabled' => true,
                'options'  => $customers,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function date_examples(): array
    {
        $colors   = ['neutral', 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error'];
        $examples = [self::example(Date::class, 'Date', 'Default', ['name' => 'date_default', 'value' => '2026-01-15'])];

        foreach ($colors as $color) {
            $examples[] = self::example(Date::class, 'Date', ucfirst($color), [
                'name'  => 'date_' . $color,
                'color' => $color,
                'value' => '2026-01-15',
            ]);
        }

        $states = [
            'Required' => [
                'name'     => 'start_date',
                'label'    => 'Start date',
                'required' => true,
            ],
            'With range' => [
                'name' => 'appointment',
                'min'  => '2026-01-01',
                'max'  => '2026-12-31',
            ],
            'Ghost' => [
                'name'  => 'filter_date',
                'ghost' => true,
            ],
            'Disabled' => [
                'name'     => 'date_disabled',
                'disabled' => true,
            ],
        ];

        foreach ($states as $title => $args) {
            $examples[] = self::example(Date::class, 'Date', $title, $args);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function datetime_examples(): array
    {
        return [
            self::example(DateTime::class, 'DateTime', 'Default', [
                'name'  => 'starts_at',
                'label' => 'Starts at',
            ]),
            self::example(DateTime::class, 'DateTime', 'With range', [
                'name'  => 'slot',
                'label' => 'Appointment slot',
                'min'   => '2026-01-01T09:00',
                'max'   => '2026-12-31T18:00',
            ]),
            self::example(DateTime::class, 'DateTime', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function time_examples(): array
    {
        return [
            self::example(Time::class, 'Time', 'Default', [
                'name'  => 'reminder',
                'label' => 'Reminder',
            ]),
            self::example(Time::class, 'Time', 'With value', [
                'name'  => 'opens',
                'label' => 'Opening time',
                'value' => '09:30',
            ]),
            self::example(Time::class, 'Time', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function color_examples(): array
    {
        return [
            self::example(Color::class, 'Color', 'Default', [
                'name'  => 'brand_color',
                'label' => 'Brand color',
                'value' => '#2563eb',
            ]),
            self::example(Color::class, 'Color', 'Large', [
                'name'  => 'accent_color',
                'label' => 'Accent color',
                'value' => '#7c3aed',
                'size'  => 'lg',
            ]),
            self::example(Color::class, 'Color', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'value'    => '#94a3b8',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function file_examples(): array
    {
        return [
            self::example(FileUpload::class, 'FileUpload', 'Default', [
                'name'  => 'avatar',
                'label' => 'Avatar',
            ]),
            self::example(FileUpload::class, 'FileUpload', 'Multiple, restricted', [
                'name'     => 'attachments',
                'label'    => 'Attachments',
                'accept'   => '.pdf,.png,.jpg',
                'multiple' => true,
            ]),
            self::example(FileUpload::class, 'FileUpload', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function checkbox_examples(): array
    {
        return [
            self::example(Checkbox::class, 'Checkbox', 'Default', [
                'name'  => 'newsletter',
                'label' => 'Subscribe to the newsletter',
            ]),
            self::example(Checkbox::class, 'Checkbox', 'Checked', [
                'name'    => 'terms',
                'label'   => 'I agree to the terms',
                'checked' => true,
                'color'   => 'success',
            ]),
            self::example(Checkbox::class, 'Checkbox', 'Disabled', [
                'name'     => 'disabled',
                'label'    => 'Disabled',
                'disabled' => true,
            ]),
        ];
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function radio_examples(): array
    {
        return [
            self::example(Radio::class, 'Radio', 'Default', [
                'name'    => 'plan_radio',
                'label'   => 'Plan',
                'value'   => 'pro',
                'options' => ['free' => 'Free', 'pro' => 'Pro', 'enterprise' => 'Enterprise'],
            ]),
            self::example(Radio::class, 'Radio', 'With error', [
                'name'    => 'shipping',
                'label'   => 'Shipping method',
                'error'   => 'Choose a shipping method.',
                'options' => ['standard' => 'Standard', 'express' => 'Express'],
            ]),
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $class, string $short_name, string $title, array $args): array
    {
        // Instantiate the real component class — the gallery can't fall
        // out of sync with the code because it IS the code.
        return [
            'title' => $title,
            'html'  => (new $class($args))->render(),
            'code'  => sprintf('(new %s(%s))->render();', $short_name, PhpLiteral::array_literal($args)),
        ];
    }
}
