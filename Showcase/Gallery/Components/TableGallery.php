<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Badge;
use Hukkoo\Components\Components\Button;
use Hukkoo\Components\Components\IconButton;
use Hukkoo\Components\Components\Modal;
use Hukkoo\Components\Components\Toast;
use Hukkoo\Components\Data\CrudTable;
use Hukkoo\Components\Data\ListTable;
use Hukkoo\Components\Data\Pagination;
use Hukkoo\Components\Data\Table;
use Hukkoo\Components\Forms\Number;
use Hukkoo\Components\Forms\Select;
use Hukkoo\Components\Forms\Text;
use Hukkoo\Components\Html;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\CodeBlock;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;
use Hukkoo\Components\Showcase\Gallery\PhpLiteral;

defined('ABSPATH') || exit;

final class TableGallery implements GalleryInterface
{
    private const COLUMNS = [
        ['key' => 'customer', 'label' => 'Customer'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status'],
    ];

    private const ROWS = [
        ['customer' => 'Acme Corp', 'email' => 'billing@acme.test', 'status' => 'Active'],
        ['customer' => 'Bluebird Studios', 'email' => 'hello@bluebird.test', 'status' => 'Active'],
        ['customer' => 'Crescent Logistics', 'email' => 'ops@crescent.test', 'status' => 'Past due'],
    ];

    // Same lists/formula the full-demo's JS uses to keep generating more
    // rows past what PHP renders for the initial (pre-JS) page — so the
    // PHP-rendered first page and the JS-rendered pages that follow it
    // are the same dataset, not two datasets that happen to look similar.
    private const DEMO_FIRST_NAMES = ['Ava', 'Liam', 'Maya', 'Noah', 'Priya', 'Ethan', 'Zoe', 'Kai', 'Ines', 'Leo', 'Sofia', 'Owen'];
    private const DEMO_LAST_NAMES = ['Turner', 'Nakamura', 'Rossi', 'Bennett', 'Kapoor', 'Silva', 'Novak', 'Bekele'];
    private const DEMO_ROLES = ['Product Designer', 'Backend Engineer', 'Support Lead', 'Marketing Manager', 'Data Analyst', 'Frontend Engineer'];
    private const DEMO_STATUSES = ['Active', 'Active', 'Active', 'Invited', 'Suspended'];

    // Same idea for the second full demo (Products) — no avatar/name-of-
    // a-person column this time, just to show the pattern isn't tied to
    // "people with pictures".
    private const DEMO_PRODUCT_NAMES = ['Wireless Mouse', 'Mechanical Keyboard', 'USB-C Hub', 'Noise Cancelling Headphones', 'Webcam HD', 'Portable SSD', 'Laptop Stand', 'Desk Lamp', 'Monitor Arm', 'Bluetooth Speaker', 'Graphics Tablet', 'Ergonomic Chair'];
    private const DEMO_STOCK_STATUSES = ['In stock', 'In stock', 'In stock', 'Low stock', 'Out of stock'];

    private const ICON_EDIT = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
    private const ICON_DELETE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m2 0-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 7h14Z"/></svg>';

    public static function slug(): string
    {
        return 'table';
    }

    public static function label(): string
    {
        return __('Table', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Basic', 'hukkoo-components'), [
                    self::example('Basic', ['columns' => self::COLUMNS, 'rows' => self::ROWS]),
                ]),
                GallerySection::render(__('Sizes', 'hukkoo-components'), self::sizes()),
                GallerySection::render(__('Modifiers', 'hukkoo-components'), self::modifiers()),
                GallerySection::render(__('With formatting', 'hukkoo-components'), [
                    self::formatting_example(),
                ]),
                GallerySection::render(__('Empty', 'hukkoo-components'), [
                    self::example('Empty', [
                        'columns'       => self::COLUMNS,
                        'rows'          => [],
                        'empty_message' => 'No customers yet.',
                    ]),
                ]),
                self::full_demo_section(),
                self::full_demo_products_section(),
                self::crud_table_section(),
                self::list_table_section(),
            ],
            ApiReference::fromReflection(Table::class)
        );
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function sizes(): array
    {
        $sizes    = ['sm', 'md', 'lg'];
        $examples = [];

        foreach ($sizes as $size) {
            $examples[] = self::example(strtoupper($size), [
                'columns' => self::COLUMNS,
                'rows'    => self::ROWS,
                'size'    => $size,
            ]);
        }

        return $examples;
    }

    /** @return array<int, array{title: string, html: string, code: string}> */
    private static function modifiers(): array
    {
        return [
            self::example('Striped', [
                'columns' => self::COLUMNS,
                'rows'    => self::ROWS,
                'striped' => true,
            ]),
            self::example('Bordered', [
                'columns'  => self::COLUMNS,
                'rows'     => self::ROWS,
                'bordered' => true,
            ]),
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function formatting_example(): array
    {
        $columns = [
            ['key' => 'customer', 'label' => 'Customer'],
            [
                'key'    => 'amount',
                'label'  => 'Amount',
                'format' => static fn ($value) => '$' . number_format((float) $value, 2),
            ],
        ];

        $rows = [
            ['customer' => 'Acme Corp', 'amount' => 1250],
            ['customer' => 'Bluebird Studios', 'amount' => 89.5],
        ];

        return [
            'title' => 'Formatted currency column',
            'html'  => (new Table(['columns' => $columns, 'rows' => $rows]))->render(),
            'code'  => "(new Table([\n"
                . "    'columns' => [\n"
                . "        ['key' => 'customer', 'label' => 'Customer'],\n"
                . "        ['key' => 'amount', 'label' => 'Amount', 'format' => fn (\$v) => '$' . number_format(\$v, 2)],\n"
                . "    ],\n"
                . "    'rows' => " . PhpLiteral::array_literal($rows) . ",\n"
                . "]))->render();",
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(string $title, array $args): array
    {
        // Instantiate the real component class — the gallery can't fall
        // out of sync with the code because it IS the code.
        return [
            'title' => $title,
            'html'  => (new Table($args))->render(),
            'code'  => sprintf('(new Table(%s))->render();', PhpLiteral::array_literal($args)),
        ];
    }

    /** @return array<int, array{id: int, name: string, role: string, email: string, status: string}> */
    private static function generate_demo_members(int $count): array
    {
        $members = [];

        for ($i = 0; $i < $count; $i++) {
            $first = self::DEMO_FIRST_NAMES[$i % count(self::DEMO_FIRST_NAMES)];
            $last  = self::DEMO_LAST_NAMES[($i * 3) % count(self::DEMO_LAST_NAMES)];

            $members[] = [
                'id'     => $i + 1,
                'name'   => "$first $last",
                'role'   => self::DEMO_ROLES[$i % count(self::DEMO_ROLES)],
                'email'  => sprintf('%s.%s@company.test', strtolower($first), strtolower($last)),
                'status' => self::DEMO_STATUSES[$i % count(self::DEMO_STATUSES)],
            ];
        }

        return $members;
    }

    /** @return array<int, array{id: int, name: string, sku: string, price: float, stock: string}> */
    private static function generate_demo_products(int $count): array
    {
        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $products[] = [
                'id'    => $i + 1,
                'name'  => self::DEMO_PRODUCT_NAMES[$i % count(self::DEMO_PRODUCT_NAMES)],
                'sku'   => sprintf('SKU-%04d', 1000 + $i),
                'price' => round(9.99 + (($i * 37) % 190), 2),
                'stock' => self::DEMO_STOCK_STATUSES[$i % count(self::DEMO_STOCK_STATUSES)],
            ];
        }

        return $products;
    }

    /**
     * A clickable column header for the full-demo tables — shared by
     * both, since the sort behavior (toggle asc/desc, one indicator per
     * header, driven entirely client-side against the in-memory array)
     * is identical regardless of which dataset it's sorting.
     */
    private static function sortable_header(string $key, string $label): Html
    {
        return Html::raw(sprintf(
            '<button type="button" class="hk-table-sort" data-hk-sort-key="%s">%s<span class="hk-sort-indicator" data-hk-sort-indicator="%s"></span></button>',
            esc_attr($key),
            esc_html($label),
            esc_attr($key)
        ));
    }

    /**
     * A full CRUD demo — search, pagination, add/edit/delete, toast —
     * combining Table, Badge, IconButton, Select, Modal and Pagination.
     * Unlike the section builders above, this doesn't fit the standard
     * "row of small examples + one shared code block" GallerySection
     * shape, so it's assembled directly as one section-shaped HTML
     * string instead of going through GallerySection::render().
     *
     * PHP renders the real first page via the real Table/Badge/IconButton
     * classes — a no-JS fallback that's an actual usable table, not an
     * empty shell. Once JS runs it takes over completely: there's no
     * server for this to round-trip to (this is a static design-system
     * showcase, not an app with a backend), so search/pagination/CRUD all
     * run against a client-side copy of the same dataset, and the row
     * markup for pages after the first is necessarily hand-built in JS
     * rather than re-invoking PHP per interaction.
     */
    private static function full_demo_section(): string
    {
        $columns = [
            [
                'key'    => 'id',
                'label'  => Html::raw('<input type="checkbox" class="hk-checkbox" id="hkdemo-check-all" aria-label="Select all">'),
                'format' => [self::class, 'render_select_cell'],
            ],
            ['key' => 'name', 'label' => self::sortable_header('name', __('Name', 'hukkoo-components')), 'format' => [self::class, 'render_name_cell']],
            ['key' => 'role', 'label' => self::sortable_header('role', __('Role', 'hukkoo-components'))],
            ['key' => 'email', 'label' => self::sortable_header('email', __('Email', 'hukkoo-components'))],
            ['key' => 'status', 'label' => self::sortable_header('status', __('Status', 'hukkoo-components')), 'format' => [self::class, 'render_status_cell']],
            [
                'key'    => 'id',
                'label'  => __('Actions', 'hukkoo-components'),
                'format' => [self::class, 'render_actions_cell'],
            ],
        ];

        $table = (new Table([
            'columns'       => $columns,
            'rows'          => self::generate_demo_members(8),
            'body_id'       => 'hkdemo-tbody',
            'empty_message' => __('No members match your search.', 'hukkoo-components'),
        ]))->render();

        $search = (new Text([
            'name'        => 'hkdemo-search',
            'placeholder' => __('Search members…', 'hukkoo-components'),
        ]))->render();

        $status_filter = (new Select([
            'name'        => 'hkdemo-filter-status',
            'placeholder' => __('All statuses', 'hukkoo-components'),
            'options'     => ['Active' => 'Active', 'Invited' => 'Invited', 'Suspended' => 'Suspended'],
        ]))->render();

        $page_size = (new Select([
            'name'    => 'hkdemo-page-size',
            'value'   => '8',
            'options' => ['5' => __('5 / page', 'hukkoo-components'), '8' => __('8 / page', 'hukkoo-components'), '12' => __('12 / page', 'hukkoo-components')],
        ]))->render();

        $add_button = (new Button([
            'label' => __('Add member', 'hukkoo-components'),
            'color' => 'primary',
            'attrs' => ['data-hk-modal-open' => 'hkdemo-member-modal', 'onclick' => 'hkdemoOpenAdd()'],
        ]))->render();

        $status_select = (new Select([
            'name'    => 'hkdemo-f-status',
            'value'   => 'Active',
            'options' => ['Active' => 'Active', 'Invited' => 'Invited', 'Suspended' => 'Suspended'],
        ]))->render();

        $member_modal = (new Modal([
            'id'      => 'hkdemo-member-modal',
            'title'   => __('Add member', 'hukkoo-components'),
            'content' => Html::raw(
                (new Text(['name' => 'hkdemo-f-name', 'label' => __('Name', 'hukkoo-components'), 'required' => true]))->render()
                . (new Text(['name' => 'hkdemo-f-role', 'label' => __('Role', 'hukkoo-components'), 'required' => true]))->render()
                . (new Text(['name' => 'hkdemo-f-email', 'label' => __('Email', 'hukkoo-components'), 'type' => 'email', 'required' => true]))->render()
                . sprintf('<div class="hk-field"><label class="hk-field-label">%s</label>%s</div>', esc_html__('Status', 'hukkoo-components'), $status_select)
                . '<input type="hidden" id="hkdemo-f-id">'
            ),
            'actions' => Html::raw(
                (new Button(['label' => __('Cancel', 'hukkoo-components'), 'style' => 'ghost', 'attrs' => ['data-hk-modal-close' => true]]))->render()
                . (new Button(['label' => __('Save', 'hukkoo-components'), 'color' => 'primary', 'attrs' => ['onclick' => 'hkdemoSaveMember()']]))->render()
            ),
        ]))->render();

        $delete_modal = (new Modal([
            'id'      => 'hkdemo-delete-modal',
            'size'    => 'sm',
            'title'   => __('Remove member?', 'hukkoo-components'),
            'content' => Html::raw(sprintf(
                '<p>%s <strong id="hkdemo-delete-name"></strong> %s</p>',
                esc_html__('This will permanently remove', 'hukkoo-components'),
                esc_html__("from the team. This can't be undone.", 'hukkoo-components')
            )),
            'actions' => Html::raw(
                (new Button(['label' => __('Cancel', 'hukkoo-components'), 'style' => 'ghost', 'attrs' => ['data-hk-modal-close' => true]]))->render()
                . (new Button(['label' => __('Delete', 'hukkoo-components'), 'color' => 'error', 'attrs' => ['onclick' => 'hkdemoConfirmDelete()']]))->render()
            ),
        ]))->render();

        $toast = (new Toast(['id' => 'hkdemo-toast']))->render();

        $body = sprintf(
            '<div class="hk-table-toolbar">'
                . '<div class="hk-table-toolbar-search">%s</div>'
                . '<div class="hk-table-toolbar-filter">%s</div>'
                . '<div class="hk-table-toolbar-spacer"></div>'
                . '%s'
                . '%s'
            . '</div>'
            . '%s'
            . '<div class="hk-table-footer">'
                . '<p class="hk-field-description hk-table-footer-label" id="hkdemo-range-label">%s</p>'
                . '<div id="hkdemo-pagination">%s</div>'
            . '</div>'
            . '%s%s%s',
            $search,
            $status_filter,
            $page_size,
            $add_button,
            $table,
            esc_html(sprintf(
                /* translators: 1: first row number, 2: last row number, 3: total row count */
                __('Showing %1$d–%2$d of %3$d', 'hukkoo-components'),
                1,
                8,
                24
            )),
            (new Pagination(['current' => 1, 'total' => 3]))->render(),
            $member_modal,
            $delete_modal,
            $toast
        );

        $body .= self::full_demo_script();

        $code = <<<'PHP'
$columns = [
    ['key' => 'name', 'label' => self::sortable_header('name', 'Name'), 'format' => [self::class, 'render_name_cell']],
    ['key' => 'role', 'label' => self::sortable_header('role', 'Role')],
    ['key' => 'email', 'label' => self::sortable_header('email', 'Email')],
    ['key' => 'status', 'label' => self::sortable_header('status', 'Status'), 'format' => [self::class, 'render_status_cell']],
];

$status_filter = (new Select([
    'name'        => 'filter-status',
    'placeholder' => 'All statuses',
    'options'     => ['Active' => 'Active', 'Invited' => 'Invited', 'Suspended' => 'Suspended'],
]))->render();

(new Table(['columns' => $columns, 'rows' => $members, 'body_id' => 'members-tbody']))->render();

// Search, the status filter, sortable_header() column clicks, pagination
// and the add/edit/delete modals all run client-side afterwards — see
// assets/js for the accompanying behavior.
PHP;

        return sprintf(
            '<section class="hk-gallery-section" id="full-example">'
                . '<h3 class="hk-gallery-section-heading"><a href="#full-example" class="hk-gallery-section-anchor">%1$s</a></h3>'
                . '<div class="hk-gallery-preview-row hk-gallery-preview-row--block">%2$s</div>'
                . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%3$s" '
                . 'data-hk-label-show="%4$s" data-hk-label-hide="%5$s" aria-expanded="false">%4$s</button>'
                . '%6$s'
            . '</section>',
            esc_html__('Full example — Team members', 'hukkoo-components'),
            $body,
            esc_attr('hk-gallery-code-full-example'),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, 'hk-gallery-code-full-example')
        );
    }

    public static function render_select_cell(int $id): Html
    {
        return Html::raw(sprintf(
            '<input type="checkbox" class="hk-checkbox hkdemo-row-check" value="%d">',
            $id
        ));
    }

    public static function render_name_cell(string $name): Html
    {
        $parts    = explode(' ', $name, 2);
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1] ?? '', 0, 1));

        return Html::raw(sprintf(
            '<div class="hk-table-cell-media">'
                . '<span class="hk-avatar">%s</span><span class="hk-table-cell-strong">%s</span>'
            . '</div>',
            esc_html($initials),
            esc_html($name)
        ));
    }

    public static function render_status_cell(string $status): Html
    {
        $color = match ($status) {
            'Active' => 'success',
            'Invited' => 'warning',
            'Suspended' => 'error',
            default => 'neutral',
        };

        return Html::raw((new Badge(['label' => $status, 'color' => $color, 'outline' => true, 'dot' => true]))->render());
    }

    public static function render_actions_cell(int $id): Html
    {
        $edit = (new IconButton([
            'icon'  => Html::raw(self::ICON_EDIT),
            'label' => __('Edit', 'hukkoo-components'),
            'style' => 'ghost',
            'size'  => 'sm',
            'attrs' => [
                'data-hk-modal-open' => 'hkdemo-member-modal',
                'onclick'            => sprintf('hkdemoEditMember(%d)', $id),
            ],
        ]))->render();

        $delete = (new IconButton([
            'icon'  => Html::raw(self::ICON_DELETE),
            'label' => __('Delete', 'hukkoo-components'),
            'style' => 'ghost',
            'color' => 'error',
            'size'  => 'sm',
            'attrs' => [
                'data-hk-modal-open' => 'hkdemo-delete-modal',
                'onclick'            => sprintf('hkdemoAskDelete(%d)', $id),
            ],
        ]))->render();

        return Html::raw(sprintf('<div class="hk-table-actions">%s%s</div>', $edit, $delete));
    }

    /**
     * A second full CRUD demo, same pattern as full_demo_section() —
     * search, pagination, add/edit/delete, toast — for a Products
     * dataset instead of Team members, deliberately without a
     * name+avatar column. Every id/class/function name is prefixed
     * `hkproddemo` rather than `hkdemo` so this table's JS (own IIFE,
     * own click/change listeners) can sit on the same page as the
     * members demo without either one's search/pagination/CRUD
     * reaching into the other's DOM.
     */
    private static function full_demo_products_section(): string
    {
        $columns = [
            [
                'key'    => 'id',
                'label'  => Html::raw('<input type="checkbox" class="hk-checkbox" id="hkproddemo-check-all" aria-label="Select all">'),
                'format' => [self::class, 'render_product_select_cell'],
            ],
            ['key' => 'name', 'label' => self::sortable_header('name', __('Name', 'hukkoo-components'))],
            ['key' => 'sku', 'label' => self::sortable_header('sku', __('SKU', 'hukkoo-components'))],
            ['key' => 'price', 'label' => self::sortable_header('price', __('Price', 'hukkoo-components')), 'format' => [self::class, 'render_price_cell']],
            ['key' => 'stock', 'label' => self::sortable_header('stock', __('Stock', 'hukkoo-components')), 'format' => [self::class, 'render_stock_cell']],
            [
                'key'    => 'id',
                'label'  => __('Actions', 'hukkoo-components'),
                'format' => [self::class, 'render_product_actions_cell'],
            ],
        ];

        $table = (new Table([
            'columns'       => $columns,
            'rows'          => self::generate_demo_products(8),
            'body_id'       => 'hkproddemo-tbody',
            'empty_message' => __('No products match your search.', 'hukkoo-components'),
        ]))->render();

        $search = (new Text([
            'name'        => 'hkproddemo-search',
            'placeholder' => __('Search products…', 'hukkoo-components'),
        ]))->render();

        $stock_filter = (new Select([
            'name'        => 'hkproddemo-filter-stock',
            'placeholder' => __('All stock levels', 'hukkoo-components'),
            'options'     => ['In stock' => 'In stock', 'Low stock' => 'Low stock', 'Out of stock' => 'Out of stock'],
        ]))->render();

        $page_size = (new Select([
            'name'    => 'hkproddemo-page-size',
            'value'   => '8',
            'options' => ['5' => __('5 / page', 'hukkoo-components'), '8' => __('8 / page', 'hukkoo-components'), '12' => __('12 / page', 'hukkoo-components')],
        ]))->render();

        $add_button = (new Button([
            'label' => __('Add product', 'hukkoo-components'),
            'color' => 'primary',
            'attrs' => ['data-hk-modal-open' => 'hkproddemo-product-modal', 'onclick' => 'hkproddemoOpenAdd()'],
        ]))->render();

        $stock_select = (new Select([
            'name'    => 'hkproddemo-f-stock',
            'value'   => 'In stock',
            'options' => ['In stock' => 'In stock', 'Low stock' => 'Low stock', 'Out of stock' => 'Out of stock'],
        ]))->render();

        $product_modal = (new Modal([
            'id'      => 'hkproddemo-product-modal',
            'title'   => __('Add product', 'hukkoo-components'),
            'content' => Html::raw(
                (new Text(['name' => 'hkproddemo-f-name', 'label' => __('Name', 'hukkoo-components'), 'required' => true]))->render()
                . (new Text(['name' => 'hkproddemo-f-sku', 'label' => __('SKU', 'hukkoo-components'), 'required' => true]))->render()
                . (new Number(['name' => 'hkproddemo-f-price', 'label' => __('Price', 'hukkoo-components'), 'min' => 0, 'step' => '0.01', 'required' => true]))->render()
                . sprintf('<div class="hk-field"><label class="hk-field-label">%s</label>%s</div>', esc_html__('Stock', 'hukkoo-components'), $stock_select)
                . '<input type="hidden" id="hkproddemo-f-id">'
            ),
            'actions' => Html::raw(
                (new Button(['label' => __('Cancel', 'hukkoo-components'), 'style' => 'ghost', 'attrs' => ['data-hk-modal-close' => true]]))->render()
                . (new Button(['label' => __('Save', 'hukkoo-components'), 'color' => 'primary', 'attrs' => ['onclick' => 'hkproddemoSaveProduct()']]))->render()
            ),
        ]))->render();

        $delete_modal = (new Modal([
            'id'      => 'hkproddemo-delete-modal',
            'size'    => 'sm',
            'title'   => __('Remove product?', 'hukkoo-components'),
            'content' => Html::raw(sprintf(
                '<p>%s <strong id="hkproddemo-delete-name"></strong> %s</p>',
                esc_html__('This will permanently remove', 'hukkoo-components'),
                esc_html__("from the catalog. This can't be undone.", 'hukkoo-components')
            )),
            'actions' => Html::raw(
                (new Button(['label' => __('Cancel', 'hukkoo-components'), 'style' => 'ghost', 'attrs' => ['data-hk-modal-close' => true]]))->render()
                . (new Button(['label' => __('Delete', 'hukkoo-components'), 'color' => 'error', 'attrs' => ['onclick' => 'hkproddemoConfirmDelete()']]))->render()
            ),
        ]))->render();

        $toast = (new Toast(['id' => 'hkproddemo-toast']))->render();

        $body = sprintf(
            '<div class="hk-table-toolbar">'
                . '<div class="hk-table-toolbar-search">%s</div>'
                . '<div class="hk-table-toolbar-filter">%s</div>'
                . '<div class="hk-table-toolbar-spacer"></div>'
                . '%s'
                . '%s'
            . '</div>'
            . '%s'
            . '<div class="hk-table-footer">'
                . '<p class="hk-field-description hk-table-footer-label" id="hkproddemo-range-label">%s</p>'
                . '<div id="hkproddemo-pagination">%s</div>'
            . '</div>'
            . '%s%s%s',
            $search,
            $stock_filter,
            $page_size,
            $add_button,
            $table,
            esc_html(sprintf(
                /* translators: 1: first row number, 2: last row number, 3: total row count */
                __('Showing %1$d–%2$d of %3$d', 'hukkoo-components'),
                1,
                8,
                24
            )),
            (new Pagination(['current' => 1, 'total' => 3]))->render(),
            $product_modal,
            $delete_modal,
            $toast
        );

        $body .= self::full_demo_products_script();

        $code = <<<'PHP'
$columns = [
    ['key' => 'name', 'label' => self::sortable_header('name', 'Name')],
    ['key' => 'sku', 'label' => self::sortable_header('sku', 'SKU')],
    ['key' => 'price', 'label' => self::sortable_header('price', 'Price'), 'format' => [self::class, 'render_price_cell']],
    ['key' => 'stock', 'label' => self::sortable_header('stock', 'Stock'), 'format' => [self::class, 'render_stock_cell']],
];

$stock_filter = (new Select([
    'name'        => 'filter-stock',
    'placeholder' => 'All stock levels',
    'options'     => ['In stock' => 'In stock', 'Low stock' => 'Low stock', 'Out of stock' => 'Out of stock'],
]))->render();

(new Table(['columns' => $columns, 'rows' => $products, 'body_id' => 'products-tbody']))->render();

// Search, the stock filter, sortable_header() column clicks (price sorts
// numerically), pagination and the add/edit/delete modals all run
// client-side afterwards — see assets/js for the accompanying behavior.
PHP;

        return sprintf(
            '<section class="hk-gallery-section" id="full-example-products">'
                . '<h3 class="hk-gallery-section-heading"><a href="#full-example-products" class="hk-gallery-section-anchor">%1$s</a></h3>'
                . '<div class="hk-gallery-preview-row hk-gallery-preview-row--block">%2$s</div>'
                . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%3$s" '
                . 'data-hk-label-show="%4$s" data-hk-label-hide="%5$s" aria-expanded="false">%4$s</button>'
                . '%6$s'
            . '</section>',
            esc_html__('Full example — Products', 'hukkoo-components'),
            $body,
            esc_attr('hk-gallery-code-full-example-products'),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, 'hk-gallery-code-full-example-products')
        );
    }

    public static function render_product_select_cell(int $id): Html
    {
        return Html::raw(sprintf(
            '<input type="checkbox" class="hk-checkbox hkproddemo-row-check" value="%d">',
            $id
        ));
    }

    public static function render_price_cell(float $price): string
    {
        return '$' . number_format($price, 2);
    }

    public static function render_stock_cell(string $stock): Html
    {
        $color = match ($stock) {
            'In stock' => 'success',
            'Low stock' => 'warning',
            'Out of stock' => 'error',
            default => 'neutral',
        };

        return Html::raw((new Badge(['label' => $stock, 'color' => $color, 'outline' => true, 'dot' => true]))->render());
    }

    public static function render_product_actions_cell(int $id): Html
    {
        $edit = (new IconButton([
            'icon'  => Html::raw(self::ICON_EDIT),
            'label' => __('Edit', 'hukkoo-components'),
            'style' => 'ghost',
            'size'  => 'sm',
            'attrs' => [
                'data-hk-modal-open' => 'hkproddemo-product-modal',
                'onclick'            => sprintf('hkproddemoEditProduct(%d)', $id),
            ],
        ]))->render();

        $delete = (new IconButton([
            'icon'  => Html::raw(self::ICON_DELETE),
            'label' => __('Delete', 'hukkoo-components'),
            'style' => 'ghost',
            'color' => 'error',
            'size'  => 'sm',
            'attrs' => [
                'data-hk-modal-open' => 'hkproddemo-delete-modal',
                'onclick'            => sprintf('hkproddemoAskDelete(%d)', $id),
            ],
        ]))->render();

        return Html::raw(sprintf('<div class="hk-table-actions">%s%s</div>', $edit, $delete));
    }

    /**
     * The two sections above build the "Full example" pattern by hand —
     * Table + Badge + IconButton + Select + Modal + Pagination, plus a
     * hand-written IIFE for search/sort/pagination/CRUD. `CrudTable`
     * packages that exact pattern into one component so a host doesn't
     * have to re-assemble it per screen; see its own gallery page
     * (Data display → CRUD Table) for the full API reference. This
     * section exists here too since anyone reaching for "Table" is
     * likely looking for this pattern and should see the productized
     * version sitting right next to the hand-built one it replaces.
     */
    private static function crud_table_section(): string
    {
        $products = self::generate_demo_products(12);

        $html = (new CrudTable([
            'id'       => 'hktable-crud-products',
            'numbered' => true,
            'columns'  => [
                ['key' => 'name', 'label' => __('Name', 'hukkoo-components'), 'sortable' => true],
                ['key' => 'sku', 'label' => __('SKU', 'hukkoo-components'), 'sortable' => true],
                [
                    'key'      => 'price',
                    'label'    => __('Price', 'hukkoo-components'),
                    'sortable' => true,
                    'format'   => [self::class, 'render_price_cell'],
                ],
                [
                    'key'      => 'stock',
                    'label'    => __('Stock', 'hukkoo-components'),
                    'sortable' => true,
                    'format'   => [self::class, 'render_stock_cell'],
                ],
            ],
            'rows'                => $products,
            'search_placeholder'  => __('Search products…', 'hukkoo-components'),
            'add_button'          => ['label' => __('Add product', 'hukkoo-components'), 'color' => 'primary'],
            'view_action'         => static fn (array $row): array => ['label' => __('View', 'hukkoo-components')],
            'edit_action'         => static fn (array $row): array => ['label' => __('Edit', 'hukkoo-components')],
            'delete_action'       => static fn (array $row): array => [
                /* translators: %s: product name */
                'title'   => sprintf(__('Remove %s?', 'hukkoo-components'), $row['name']),
                'message' => __("This will permanently remove it from the catalog. This can't be undone.", 'hukkoo-components'),
                'form'    => Html::raw(''),
            ],
        ]))->render();

        $intro = sprintf(
            '<p class="hk-field-description">%s</p>',
            esc_html__(
                'Same search + sortable columns + Add + View/Edit/Delete + pagination pattern as the two examples above — as one component instead of hand-assembled Table/Badge/IconButton/Modal/Pagination markup and a page-specific script.',
                'hukkoo-components'
            )
        );

        $code = <<<'PHP'
(new CrudTable([
    'id'       => 'products',
    'numbered' => true,
    'columns'  => [
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'sku', 'label' => 'SKU', 'sortable' => true],
        ['key' => 'price', 'label' => 'Price', 'sortable' => true, 'format' => [self::class, 'render_price_cell']],
        ['key' => 'stock', 'label' => 'Stock', 'sortable' => true, 'format' => [self::class, 'render_stock_cell']],
    ],
    'rows'          => $products,
    'add_button'    => ['label' => 'Add product', 'color' => 'primary', 'url' => '...'],
    'view_action'   => fn ($row) => ['label' => 'View', 'url' => '...' . $row['id']],
    'edit_action'   => fn ($row) => ['label' => 'Edit', 'url' => '...' . $row['id']],
    'delete_action' => fn ($row) => [
        'title'   => "Remove {$row['name']}?",
        'message' => "This will permanently remove it from the catalog. This can't be undone.",
        'form'    => Html::raw('<input type="hidden" name="action" value="delete_product" />' . wp_nonce_field(...)),
    ],
]))->render();

// Search, sortable columns and pagination all run client-side — see
// Data display → CRUD Table for the full API reference.
PHP;

        return sprintf(
            '<section class="hk-gallery-section" id="full-example-crud-table">'
                . '<h3 class="hk-gallery-section-heading"><a href="#full-example-crud-table" class="hk-gallery-section-anchor">%1$s</a></h3>'
                . '%2$s'
                . '<div class="hk-gallery-preview-row hk-gallery-preview-row--block">%3$s</div>'
                . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%4$s" '
                . 'data-hk-label-show="%5$s" data-hk-label-hide="%6$s" aria-expanded="false">%5$s</button>'
                . '%7$s'
            . '</section>',
            esc_html__('Full example — CrudTable component', 'hukkoo-components'),
            $intro,
            $html,
            esc_attr('hk-gallery-code-full-example-crud-table'),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, 'hk-gallery-code-full-example-crud-table')
        );
    }

    /**
     * `CrudTable` above is client-side: hand the whole dataset to the
     * browser once and let JS filter/sort/paginate it. `ListTable` is the
     * server-driven alternative — every search, column-sort and page
     * click here is a real navigation (read from $_GET, re-filtered by
     * plain PHP against this in-memory array, exactly the shape a real
     * SQL-backed host would take), the same as `hukkoo-core`'s own
     * per-table records screens. Query args are prefixed hktablelt_ so
     * they don't collide with this page's own ?tab= routing.
     */
    private static function list_table_section(): string
    {
        $perPage = 5;
        $all     = self::generate_demo_products(12);

        $search  = isset($_GET['hktablelt_s']) ? sanitize_text_field(wp_unslash($_GET['hktablelt_s'])) : '';
        $orderby = isset($_GET['hktablelt_orderby']) ? sanitize_key($_GET['hktablelt_orderby']) : 'id';
        $order   = isset($_GET['hktablelt_order']) && 'desc' === strtolower((string) $_GET['hktablelt_order']) ? 'DESC' : 'ASC';
        $page    = max(1, (int) ($_GET['hktablelt_paged'] ?? 1));

        if (!in_array($orderby, ['id', 'name', 'sku', 'price', 'stock'], true)) {
            $orderby = 'id';
        }

        $rows = $all;
        if ('' !== $search) {
            $needle = strtolower($search);
            $rows   = array_values(array_filter(
                $rows,
                static fn (array $row): bool => false !== strpos(strtolower($row['name']), $needle)
                    || false !== strpos(strtolower($row['sku']), $needle)
            ));
        }

        usort($rows, static function (array $a, array $b) use ($orderby, $order): int {
            $result = $a[$orderby] <=> $b[$orderby];

            return 'DESC' === $order ? -$result : $result;
        });

        $total      = count($rows);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);
        $pageRows   = array_map(
            static fn (array $row): array => $row + ['_id' => $row['id']],
            array_slice($rows, ($page - 1) * $perPage, $perPage)
        );

        $baseUrl = remove_query_arg(['hktablelt_s', 'hktablelt_orderby', 'hktablelt_order', 'hktablelt_paged']);

        $sortUrl = static function (string $key) use ($baseUrl, $orderby, $order, $search): string {
            $nextOrder = ($key === $orderby && 'ASC' === $order) ? 'desc' : 'asc';
            $args      = ['hktablelt_orderby' => $key, 'hktablelt_order' => $nextOrder];
            if ('' !== $search) {
                $args['hktablelt_s'] = $search;
            }

            return add_query_arg($args, $baseUrl);
        };

        $paginationUrl = static function (int $targetPage) use ($baseUrl, $orderby, $order, $search): string {
            $args = ['hktablelt_paged' => $targetPage, 'hktablelt_orderby' => $orderby, 'hktablelt_order' => strtolower($order)];
            if ('' !== $search) {
                $args['hktablelt_s'] = $search;
            }

            return add_query_arg($args, $baseUrl);
        };

        $html = (new ListTable([
            'id'      => 'hktable-list-products',
            'columns' => [
                ['key' => 'name', 'label' => __('Name', 'hukkoo-components'), 'sortable' => true],
                ['key' => 'sku', 'label' => __('SKU', 'hukkoo-components'), 'sortable' => true],
                [
                    'key'      => 'price',
                    'label'    => __('Price', 'hukkoo-components'),
                    'sortable' => true,
                    'format'   => [self::class, 'render_price_cell'],
                ],
                [
                    'key'      => 'stock',
                    'label'    => __('Stock', 'hukkoo-components'),
                    'sortable' => true,
                    'format'   => [self::class, 'render_stock_cell'],
                ],
            ],
            'rows'                => $pageRows,
            'current_sort_key'    => $orderby,
            'current_sort_dir'    => strtolower($order),
            'sort_url'            => $sortUrl,
            'search_value'        => $search,
            'search_placeholder'  => __('Search products…', 'hukkoo-components'),
            'search_action'       => $baseUrl,
            'add_button'          => ['label' => __('Add product', 'hukkoo-components'), 'color' => 'primary'],
            'edit_action'         => static fn (array $row): array => ['label' => __('Edit', 'hukkoo-components')],
            'delete_action'       => static fn (array $row): array => [
                'label'   => __('Delete', 'hukkoo-components'),
                'url'     => '#',
                'confirm' => __('Remove this product?', 'hukkoo-components'),
            ],
            'bulk_actions'        => ['bulk_delete' => __('Delete', 'hukkoo-components')],
            'bulk_action_url'     => $baseUrl,
            'pagination'          => [
                'current'     => $page,
                'total_pages' => $totalPages,
                'url'         => $paginationUrl,
            ],
            'total_label'         => sprintf(
                /* translators: %d: total record count */
                _n('%d item', '%d items', $total, 'hukkoo-components'),
                $total
            ),
        ]))->render();

        $intro = sprintf(
            '<p class="hk-field-description">%s</p>',
            esc_html__(
                'Same shape as CrudTable above, but server-driven: search, column sort and pagination are real navigations against this dataset (read from $_GET, re-queried with plain PHP) rather than client-side JS — the pattern for a host with its own SQL query behind the table. See Data display → List Table for the full API reference.',
                'hukkoo-components'
            )
        );

        $code = <<<'PHP'
(new ListTable([
    'id'               => 'products',
    'columns'          => [
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'sku', 'label' => 'SKU', 'sortable' => true],
        ['key' => 'price', 'label' => 'Price', 'sortable' => true, 'format' => [self::class, 'render_price_cell']],
        ['key' => 'stock', 'label' => 'Stock', 'sortable' => true, 'format' => [self::class, 'render_stock_cell']],
    ],
    'rows'             => $products,          // already the current page, from a real SQL query
    'current_sort_key' => $orderby,
    'current_sort_dir' => strtolower($order),
    'sort_url'         => fn ($key) => add_query_arg(['orderby' => $key, 'order' => ...], $baseUrl),
    'search_value'     => $search,
    'add_button'       => ['label' => 'Add product', 'url' => '...'],
    'edit_action'      => fn ($row) => ['label' => 'Edit', 'url' => '...' . $row['_id']],
    'delete_action'    => fn ($row) => ['label' => 'Delete', 'url' => '...', 'confirm' => 'Remove this product?'],
    'bulk_actions'     => ['bulk_delete' => 'Delete'],
    'bulk_action_url'  => admin_url('admin-post.php'),
    'pagination'       => ['current' => $page, 'total_pages' => $totalPages, 'url' => fn ($p) => ...],
    'total_label'      => "{$total} items",
]))->render();

// Search, column-sort and pagination all trigger a real page load — see
// Data display → List Table for the full API reference.
PHP;

        return sprintf(
            '<section class="hk-gallery-section" id="full-example-list-table">'
                . '<h3 class="hk-gallery-section-heading"><a href="#full-example-list-table" class="hk-gallery-section-anchor">%1$s</a></h3>'
                . '%2$s'
                . '<div class="hk-gallery-preview-row hk-gallery-preview-row--block">%3$s</div>'
                . '<button type="button" class="hk-gallery-code-toggle" data-hk-disclosure-toggle="%4$s" '
                . 'data-hk-label-show="%5$s" data-hk-label-hide="%6$s" aria-expanded="false">%5$s</button>'
                . '%7$s'
            . '</section>',
            esc_html__('Full example — ListTable component', 'hukkoo-components'),
            $intro,
            $html,
            esc_attr('hk-gallery-code-full-example-list-table'),
            esc_attr__('Show code', 'hukkoo-components'),
            esc_attr__('Hide code', 'hukkoo-components'),
            CodeBlock::render($code, 'hk-gallery-code-full-example-list-table')
        );
    }

    private static function full_demo_products_script(): string
    {
        return <<<'HTML'
<script>
(function () {
	var productNames = ["Wireless Mouse","Mechanical Keyboard","USB-C Hub","Noise Cancelling Headphones","Webcam HD","Portable SSD","Laptop Stand","Desk Lamp","Monitor Arm","Bluetooth Speaker","Graphics Tablet","Ergonomic Chair"];
	var stockStatuses = ["In stock","In stock","In stock","Low stock","Out of stock"];
	var iconEdit = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
	var iconDelete = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m2 0-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 7h14Z"/></svg>';

	// Same formula generate_demo_products() uses in PHP — see the members
	// demo's script for why this matters (JS continues the same dataset
	// PHP rendered the first page of, rather than a different one).
	var products = [];
	for (var i = 0; i < 24; i++) {
		products.push({
			id: i + 1,
			name: productNames[i % productNames.length],
			sku: 'SKU-' + (1000 + i),
			price: Math.round((9.99 + ((i * 37) % 190)) * 100) / 100,
			stock: stockStatuses[i % stockStatuses.length]
		});
	}

	var page = 1;
	var pageSize = 8;
	var pendingDeleteId = null;
	var sortKey = null;
	var sortDir = 1;
	var filterStock = '';

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function stockColor(stock) {
		return { 'In stock': 'success', 'Low stock': 'warning', 'Out of stock': 'error' }[stock] || 'neutral';
	}

	function formatPrice(price) {
		return '$' + Number(price).toFixed(2);
	}

	function currentSorted(list) {
		if (!sortKey) return list;
		return list.slice().sort(function (a, b) {
			var av = a[sortKey];
			var bv = b[sortKey];
			if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * sortDir;
			return String(av).toLowerCase().localeCompare(String(bv).toLowerCase()) * sortDir;
		});
	}

	function updateSortIndicators() {
		var table = document.getElementById('hkproddemo-tbody').closest('table');
		table.querySelectorAll('[data-hk-sort-indicator]').forEach(function (el) {
			var key = el.getAttribute('data-hk-sort-indicator');
			el.textContent = key === sortKey ? (sortDir === 1 ? '▲' : '▼') : '';
		});
	}

	function rowHtml(p) {
		return ''
			+ '<tr>'
			+ '<td><input type="checkbox" class="hk-checkbox hkproddemo-row-check" value="' + p.id + '"></td>'
			+ '<td class="hk-table-cell-strong">' + escapeHtml(p.name) + '</td>'
			+ '<td>' + escapeHtml(p.sku) + '</td>'
			+ '<td>' + escapeHtml(formatPrice(p.price)) + '</td>'
			+ '<td><span class="hk-badge hk-badge--' + stockColor(p.stock) + ' hk-badge--outline"><span class="hk-badge-dot" aria-hidden="true"></span>' + escapeHtml(p.stock) + '</span></td>'
			+ '<td><div class="hk-table-actions">'
			+ '<button type="button" class="hk-button hk-button--square hk-button--ghost hk-button--sm" aria-label="Edit" data-hk-modal-open="hkproddemo-product-modal" onclick="hkproddemoEditProduct(' + p.id + ')">' + iconEdit + '</button>'
			+ '<button type="button" class="hk-button hk-button--square hk-button--ghost hk-button--error hk-button--sm" aria-label="Delete" data-hk-modal-open="hkproddemo-delete-modal" onclick="hkproddemoAskDelete(' + p.id + ')">' + iconDelete + '</button>'
			+ '</div></td>'
			+ '</tr>';
	}

	function currentFiltered() {
		var q = (document.getElementById('hkproddemo-search').value || '').trim().toLowerCase();
		var list = products;
		if (filterStock) {
			list = list.filter(function (p) { return p.stock === filterStock; });
		}
		if (!q) return list;
		return list.filter(function (p) {
			return p.name.toLowerCase().indexOf(q) !== -1 || p.sku.toLowerCase().indexOf(q) !== -1;
		});
	}

	function paginationHtml(current, total) {
		function item(label, target, disabled, active) {
			return '<button type="button" class="hk-pagination-item' + (active ? ' hk-pagination-item--active' : '') + '" data-hk-prod-demo-page="' + target + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
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

	function render() {
		var filtered = currentSorted(currentFiltered());
		var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
		if (page > totalPages) page = totalPages;

		var start = (page - 1) * pageSize;
		var pageItems = filtered.slice(start, start + pageSize);
		var tbody = document.getElementById('hkproddemo-tbody');

		tbody.innerHTML = pageItems.length
			? pageItems.map(rowHtml).join('')
			: '<tr><td colspan="6" class="hk-table-empty">No products match your search.</td></tr>';

		document.getElementById('hkproddemo-range-label').textContent = filtered.length === 0
			? 'No results'
			: 'Showing ' + (start + 1) + '–' + Math.min(start + pageSize, filtered.length) + ' of ' + filtered.length;

		document.getElementById('hkproddemo-pagination').innerHTML = paginationHtml(page, totalPages);

		var checkAll = document.getElementById('hkproddemo-check-all');
		if (checkAll) checkAll.checked = false;

		updateSortIndicators();
	}

	function setSelectValue(root, value) {
		var hidden = root.querySelector('[data-hk-select-value-input]');
		var label = root.querySelector('.hk-select-value');
		var option = root.querySelector('[data-hk-select-option="' + value + '"]');
		if (hidden) hidden.value = value;
		if (label && option) label.textContent = option.textContent;
		root.querySelectorAll('[data-hk-select-option]').forEach(function (el) {
			el.setAttribute('aria-selected', el.getAttribute('data-hk-select-option') === value ? 'true' : 'false');
		});
	}

	function stockSelectRoot() {
		return document.getElementById('hkproddemo-f-stock').closest('.hk-select');
	}

	function closeModal(id) {
		var closeBtn = document.querySelector('#' + id + ' [data-hk-modal-close]');
		if (closeBtn) closeBtn.click();
	}

	window.hkproddemoOpenAdd = function () {
		document.getElementById('hkproddemo-product-modal-title').textContent = 'Add product';
		document.getElementById('hkproddemo-f-id').value = '';
		document.getElementById('hkproddemo-f-name').value = '';
		document.getElementById('hkproddemo-f-sku').value = '';
		document.getElementById('hkproddemo-f-price').value = '';
		setSelectValue(stockSelectRoot(), 'In stock');
	};

	window.hkproddemoEditProduct = function (id) {
		var p = products.filter(function (x) { return x.id === id; })[0];
		if (!p) return;
		document.getElementById('hkproddemo-product-modal-title').textContent = 'Edit product';
		document.getElementById('hkproddemo-f-id').value = p.id;
		document.getElementById('hkproddemo-f-name').value = p.name;
		document.getElementById('hkproddemo-f-sku').value = p.sku;
		document.getElementById('hkproddemo-f-price').value = p.price;
		setSelectValue(stockSelectRoot(), p.stock);
	};

	window.hkproddemoSaveProduct = function () {
		var id = document.getElementById('hkproddemo-f-id').value;
		var stockInput = document.querySelector('[name="hkproddemo-f-stock"]');
		var data = {
			name: document.getElementById('hkproddemo-f-name').value.trim(),
			sku: document.getElementById('hkproddemo-f-sku').value.trim(),
			price: parseFloat(document.getElementById('hkproddemo-f-price').value) || 0,
			stock: stockInput ? stockInput.value : 'In stock'
		};
		if (!data.name || !data.sku) return;

		if (id) {
			var p = products.filter(function (x) { return x.id === parseInt(id, 10); })[0];
			if (p) Object.assign(p, data);
			hkToast(data.name + ' updated', 'success', 'hkproddemo-toast');
		} else {
			var newId = products.reduce(function (max, x) { return Math.max(max, x.id); }, 0) + 1;
			products.unshift(Object.assign({ id: newId }, data));
			hkToast(data.name + ' added', 'success', 'hkproddemo-toast');
			page = 1;
		}

		closeModal('hkproddemo-product-modal');
		render();
	};

	window.hkproddemoAskDelete = function (id) {
		var p = products.filter(function (x) { return x.id === id; })[0];
		if (!p) return;
		pendingDeleteId = id;
		document.getElementById('hkproddemo-delete-name').textContent = p.name;
	};

	window.hkproddemoConfirmDelete = function () {
		var p = products.filter(function (x) { return x.id === pendingDeleteId; })[0];
		products = products.filter(function (x) { return x.id !== pendingDeleteId; });
		closeModal('hkproddemo-delete-modal');
		if (p) hkToast(p.name + ' removed', 'error', 'hkproddemo-toast');
		pendingDeleteId = null;
		render();
	};

	document.getElementById('hkproddemo-search').addEventListener('input', function () {
		page = 1;
		render();
	});

	document.addEventListener('change', function (e) {
		if (e.target.matches('[name="hkproddemo-page-size"]')) {
			pageSize = parseInt(e.target.value, 10);
			page = 1;
			render();
		}
		if (e.target.matches('[name="hkproddemo-filter-stock"]')) {
			filterStock = e.target.value;
			page = 1;
			render();
		}
	});

	document.addEventListener('click', function (e) {
		var sortBtn = e.target.closest('[data-hk-sort-key]');
		if (sortBtn && sortBtn.closest('table') === document.getElementById('hkproddemo-tbody').closest('table')) {
			var key = sortBtn.getAttribute('data-hk-sort-key');
			if (sortKey === key) {
				sortDir = -sortDir;
			} else {
				sortKey = key;
				sortDir = 1;
			}
			render();
			return;
		}
		var pageBtn = e.target.closest('[data-hk-prod-demo-page]');
		if (pageBtn && !pageBtn.disabled) {
			page = parseInt(pageBtn.getAttribute('data-hk-prod-demo-page'), 10);
			render();
			return;
		}
		if (e.target.id === 'hkproddemo-check-all') {
			document.querySelectorAll('.hkproddemo-row-check').forEach(function (c) {
				c.checked = e.target.checked;
			});
		}
	});

	// Deliberately not calling render() here either — see the members
	// demo's script for why.
})();
</script>
HTML;
    }

    private static function full_demo_script(): string
    {
        return <<<'HTML'
<script>
(function () {
	var firstNames = ["Ava","Liam","Maya","Noah","Priya","Ethan","Zoe","Kai","Ines","Leo","Sofia","Owen"];
	var lastNames = ["Turner","Nakamura","Rossi","Bennett","Kapoor","Silva","Novak","Bekele"];
	var roles = ["Product Designer","Backend Engineer","Support Lead","Marketing Manager","Data Analyst","Frontend Engineer"];
	var statuses = ["Active","Active","Active","Invited","Suspended"];
	var iconEdit = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
	var iconDelete = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hk-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m2 0-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 7h14Z"/></svg>';

	// Same formula generate_demo_members() uses in PHP, so the rows JS
	// takes over with (index 8 onward) are a continuation of the same
	// dataset PHP rendered the first page of, not a different one.
	var members = [];
	for (var i = 0; i < 24; i++) {
		var fn = firstNames[i % firstNames.length];
		var ln = lastNames[(i * 3) % lastNames.length];
		members.push({
			id: i + 1,
			name: fn + ' ' + ln,
			role: roles[i % roles.length],
			email: fn.toLowerCase() + '.' + ln.toLowerCase() + '@company.test',
			status: statuses[i % statuses.length]
		});
	}

	var page = 1;
	var pageSize = 8;
	var pendingDeleteId = null;
	var sortKey = null;
	var sortDir = 1;
	var filterStatus = '';

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function statusColor(status) {
		return { Active: 'success', Invited: 'warning', Suspended: 'error' }[status] || 'neutral';
	}

	function currentSorted(list) {
		if (!sortKey) return list;
		var copy = list.slice();
		copy.sort(function (a, b) {
			var av = a[sortKey];
			var bv = b[sortKey];
			if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * sortDir;
			av = String(av).toLowerCase();
			bv = String(bv).toLowerCase();
			if (av < bv) return -1 * sortDir;
			if (av > bv) return 1 * sortDir;
			return 0;
		});
		return copy;
	}

	function updateSortIndicators() {
		var table = document.getElementById('hkdemo-tbody').closest('table');
		table.querySelectorAll('[data-hk-sort-indicator]').forEach(function (el) {
			var key = el.getAttribute('data-hk-sort-indicator');
			el.textContent = key === sortKey ? (sortDir === 1 ? '▲' : '▼') : '';
		});
	}

	function initials(name) {
		return name.split(' ').slice(0, 2).map(function (p) { return p[0]; }).join('').toUpperCase();
	}

	function rowHtml(m) {
		return ''
			+ '<tr>'
			+ '<td><input type="checkbox" class="hk-checkbox hkdemo-row-check" value="' + m.id + '"></td>'
			+ '<td><div class="hk-table-cell-media">'
			+ '<span class="hk-avatar">' + escapeHtml(initials(m.name)) + '</span>'
			+ '<span class="hk-table-cell-strong">' + escapeHtml(m.name) + '</span></div></td>'
			+ '<td>' + escapeHtml(m.role) + '</td>'
			+ '<td>' + escapeHtml(m.email) + '</td>'
			+ '<td><span class="hk-badge hk-badge--' + statusColor(m.status) + ' hk-badge--outline"><span class="hk-badge-dot" aria-hidden="true"></span>' + escapeHtml(m.status) + '</span></td>'
			+ '<td><div class="hk-table-actions">'
			+ '<button type="button" class="hk-button hk-button--square hk-button--ghost hk-button--sm" aria-label="Edit" data-hk-modal-open="hkdemo-member-modal" onclick="hkdemoEditMember(' + m.id + ')">' + iconEdit + '</button>'
			+ '<button type="button" class="hk-button hk-button--square hk-button--ghost hk-button--error hk-button--sm" aria-label="Delete" data-hk-modal-open="hkdemo-delete-modal" onclick="hkdemoAskDelete(' + m.id + ')">' + iconDelete + '</button>'
			+ '</div></td>'
			+ '</tr>';
	}

	function currentFiltered() {
		var q = (document.getElementById('hkdemo-search').value || '').trim().toLowerCase();
		var list = members;
		if (filterStatus) {
			list = list.filter(function (m) { return m.status === filterStatus; });
		}
		if (q) {
			list = list.filter(function (m) {
				return m.name.toLowerCase().indexOf(q) !== -1
					|| m.role.toLowerCase().indexOf(q) !== -1
					|| m.email.toLowerCase().indexOf(q) !== -1;
			});
		}
		return list;
	}

	function paginationHtml(current, total) {
		function item(label, target, disabled, active) {
			return '<button type="button" class="hk-pagination-item' + (active ? ' hk-pagination-item--active' : '') + '" data-hk-demo-page="' + target + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
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

	function render() {
		var filtered = currentSorted(currentFiltered());
		var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
		if (page > totalPages) page = totalPages;

		var start = (page - 1) * pageSize;
		var pageItems = filtered.slice(start, start + pageSize);
		var tbody = document.getElementById('hkdemo-tbody');

		tbody.innerHTML = pageItems.length
			? pageItems.map(rowHtml).join('')
			: '<tr><td colspan="6" class="hk-table-empty">No members match your search.</td></tr>';

		document.getElementById('hkdemo-range-label').textContent = filtered.length === 0
			? 'No results'
			: 'Showing ' + (start + 1) + '–' + Math.min(start + pageSize, filtered.length) + ' of ' + filtered.length;

		document.getElementById('hkdemo-pagination').innerHTML = paginationHtml(page, totalPages);

		var checkAll = document.getElementById('hkdemo-check-all');
		if (checkAll) checkAll.checked = false;

		updateSortIndicators();
	}

	function setSelectValue(root, value) {
		var hidden = root.querySelector('[data-hk-select-value-input]');
		var label = root.querySelector('.hk-select-value');
		var option = root.querySelector('[data-hk-select-option="' + value + '"]');
		if (hidden) hidden.value = value;
		if (label && option) label.textContent = option.textContent;
		root.querySelectorAll('[data-hk-select-option]').forEach(function (el) {
			el.setAttribute('aria-selected', el.getAttribute('data-hk-select-option') === value ? 'true' : 'false');
		});
	}

	function statusSelectRoot() {
		return document.getElementById('hkdemo-f-status').closest('.hk-select');
	}

	function closeModal(id) {
		var closeBtn = document.querySelector('#' + id + ' [data-hk-modal-close]');
		if (closeBtn) closeBtn.click();
	}

	window.hkdemoOpenAdd = function () {
		document.getElementById('hkdemo-member-modal-title').textContent = 'Add member';
		document.getElementById('hkdemo-f-id').value = '';
		document.getElementById('hkdemo-f-name').value = '';
		document.getElementById('hkdemo-f-role').value = '';
		document.getElementById('hkdemo-f-email').value = '';
		setSelectValue(statusSelectRoot(), 'Active');
	};

	window.hkdemoEditMember = function (id) {
		var m = members.filter(function (x) { return x.id === id; })[0];
		if (!m) return;
		document.getElementById('hkdemo-member-modal-title').textContent = 'Edit member';
		document.getElementById('hkdemo-f-id').value = m.id;
		document.getElementById('hkdemo-f-name').value = m.name;
		document.getElementById('hkdemo-f-role').value = m.role;
		document.getElementById('hkdemo-f-email').value = m.email;
		setSelectValue(statusSelectRoot(), m.status);
	};

	window.hkdemoSaveMember = function () {
		var id = document.getElementById('hkdemo-f-id').value;
		var statusInput = document.querySelector('[name="hkdemo-f-status"]');
		var data = {
			name: document.getElementById('hkdemo-f-name').value.trim(),
			role: document.getElementById('hkdemo-f-role').value.trim(),
			email: document.getElementById('hkdemo-f-email').value.trim(),
			status: statusInput ? statusInput.value : 'Active'
		};
		if (!data.name || !data.role || !data.email) return;

		if (id) {
			var m = members.filter(function (x) { return x.id === parseInt(id, 10); })[0];
			if (m) Object.assign(m, data);
			hkToast(data.name + ' updated', 'success', 'hkdemo-toast');
		} else {
			var newId = members.reduce(function (max, x) { return Math.max(max, x.id); }, 0) + 1;
			members.unshift(Object.assign({ id: newId }, data));
			hkToast(data.name + ' added', 'success', 'hkdemo-toast');
			page = 1;
		}

		closeModal('hkdemo-member-modal');
		render();
	};

	window.hkdemoAskDelete = function (id) {
		var m = members.filter(function (x) { return x.id === id; })[0];
		if (!m) return;
		pendingDeleteId = id;
		document.getElementById('hkdemo-delete-name').textContent = m.name;
	};

	window.hkdemoConfirmDelete = function () {
		var m = members.filter(function (x) { return x.id === pendingDeleteId; })[0];
		members = members.filter(function (x) { return x.id !== pendingDeleteId; });
		closeModal('hkdemo-delete-modal');
		if (m) hkToast(m.name + ' removed', 'error', 'hkdemo-toast');
		pendingDeleteId = null;
		render();
	};

	document.getElementById('hkdemo-search').addEventListener('input', function () {
		page = 1;
		render();
	});

	document.addEventListener('change', function (e) {
		if (e.target.matches('[name="hkdemo-page-size"]')) {
			pageSize = parseInt(e.target.value, 10);
			page = 1;
			render();
		}
		if (e.target.matches('[name="hkdemo-filter-status"]')) {
			filterStatus = e.target.value;
			page = 1;
			render();
		}
	});

	document.addEventListener('click', function (e) {
		var sortBtn = e.target.closest('[data-hk-sort-key]');
		if (sortBtn && sortBtn.closest('table') === document.getElementById('hkdemo-tbody').closest('table')) {
			var key = sortBtn.getAttribute('data-hk-sort-key');
			if (sortKey === key) {
				sortDir = -sortDir;
			} else {
				sortKey = key;
				sortDir = 1;
			}
			render();
			return;
		}
		var pageBtn = e.target.closest('[data-hk-demo-page]');
		if (pageBtn && !pageBtn.disabled) {
			page = parseInt(pageBtn.getAttribute('data-hk-demo-page'), 10);
			render();
			return;
		}
		if (e.target.id === 'hkdemo-check-all') {
			document.querySelectorAll('.hkdemo-row-check').forEach(function (c) {
				c.checked = e.target.checked;
			});
		}
	});

	// Deliberately not calling render() here: the first page PHP already
	// rendered (real Table/Badge/IconButton output, matching this same
	// dataset) stays the visible truth until the user actually does
	// something — search, filter, sort, page, edit, or delete — that
	// needs it replaced.
})();
</script>
HTML;
    }
}
