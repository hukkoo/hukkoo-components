<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Badge;
use Hukkoo\Components\Data\ListTable;
use Hukkoo\Components\Html;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;

defined('ABSPATH') || exit;

/**
 * Unlike CrudTable's gallery (a static dataset re-filtered by JS already
 * on the page), this one is genuinely server-driven: search/sort/page
 * clicks are real navigations back to this same Showcase page, read from
 * $_GET (prefixed hklt_ so they don't collide with the Showcase's own
 * ?tab= routing), and re-query this in-memory dataset with plain PHP —
 * the same shape a real host's SQL-backed query would take.
 */
final class ListTableGallery implements GalleryInterface
{
    private const NAMES    = ['Ava Turner', 'Liam Bennett', 'Maya Novak', 'Noah Nakamura', 'Priya Kapoor', 'Ethan Bekele', 'Zoe Rossi', 'Kai Silva', 'Ines Costa', 'Leo Fischer', 'Sofia Reyes', 'Owen Clark', 'Mia Chen', 'Jack Ryan', 'Ana Petrova'];
    private const ROLES    = ['Product Designer', 'Backend Engineer', 'Support Lead', 'Marketing Manager', 'Data Analyst', 'Frontend Engineer'];
    private const STATUSES = ['Active', 'Active', 'Active', 'Invited', 'Suspended'];

    public static function slug(): string
    {
        return 'list-table';
    }

    public static function label(): string
    {
        return __('List Table', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('Full example', 'hukkoo-components'), [
                    self::example(),
                ]),
            ],
            ApiReference::fromReflection(ListTable::class)
        );
    }

    /** @return array<int, array{id: int, name: string, role: string, email: string, status: string}> */
    private static function dataset(): array
    {
        $rows = [];
        foreach (self::NAMES as $i => $name) {
            $rows[] = [
                'id'     => $i + 1,
                'name'   => $name,
                'role'   => self::ROLES[$i % count(self::ROLES)],
                'email'  => sprintf('%s@company.test', str_replace(' ', '.', strtolower($name))),
                'status' => self::STATUSES[$i % count(self::STATUSES)],
            ];
        }

        return $rows;
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(): array
    {
        $perPage = 5;

        $search  = isset($_GET['hklt_s']) ? sanitize_text_field(wp_unslash($_GET['hklt_s'])) : '';
        $orderby = isset($_GET['hklt_orderby']) ? sanitize_key($_GET['hklt_orderby']) : 'id';
        $order   = isset($_GET['hklt_order']) && 'desc' === strtolower((string) $_GET['hklt_order']) ? 'DESC' : 'ASC';
        $page    = max(1, (int) ($_GET['hklt_paged'] ?? 1));

        if (!in_array($orderby, ['id', 'name', 'role', 'email', 'status'], true)) {
            $orderby = 'id';
        }

        $rows = self::dataset();

        if ('' !== $search) {
            $needle = strtolower($search);
            $rows   = array_values(array_filter(
                $rows,
                static fn (array $row): bool => false !== strpos(strtolower($row['name']), $needle)
                    || false !== strpos(strtolower($row['role']), $needle)
                    || false !== strpos(strtolower($row['email']), $needle)
            ));
        }

        usort($rows, static function (array $a, array $b) use ($orderby, $order): int {
            $result = $a[$orderby] <=> $b[$orderby];

            return 'DESC' === $order ? -$result : $result;
        });

        $total      = count($rows);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);
        $pageRows   = array_slice($rows, ($page - 1) * $perPage, $perPage);

        $baseUrl = remove_query_arg(['hklt_s', 'hklt_orderby', 'hklt_order', 'hklt_paged']);

        $sortUrl = static function (string $key) use ($baseUrl, $orderby, $order, $search): string {
            $nextOrder = ($key === $orderby && 'ASC' === $order) ? 'desc' : 'asc';
            $args      = ['hklt_orderby' => $key, 'hklt_order' => $nextOrder];
            if ('' !== $search) {
                $args['hklt_s'] = $search;
            }

            return add_query_arg($args, $baseUrl);
        };

        $paginationUrl = static function (int $targetPage) use ($baseUrl, $orderby, $order, $search): string {
            $args = ['hklt_paged' => $targetPage, 'hklt_orderby' => $orderby, 'hklt_order' => strtolower($order)];
            if ('' !== $search) {
                $args['hklt_s'] = $search;
            }

            return add_query_arg($args, $baseUrl);
        };

        $tableRows = array_map(
            static fn (array $row): array => $row + ['_id' => $row['id']],
            $pageRows
        );

        $html = (new ListTable([
            'id'      => 'hkgallery-list-table',
            'columns' => [
                ['key' => 'id', 'label' => __('ID', 'hukkoo-components'), 'sortable' => true],
                ['key' => 'name', 'label' => __('Name', 'hukkoo-components'), 'sortable' => true],
                ['key' => 'role', 'label' => __('Role', 'hukkoo-components'), 'sortable' => true],
                ['key' => 'email', 'label' => __('Email', 'hukkoo-components'), 'sortable' => true],
                [
                    'key'      => 'status',
                    'label'    => __('Status', 'hukkoo-components'),
                    'sortable' => true,
                    'format'   => static fn (string $status): Html => Html::raw((new Badge([
                        'label'   => $status,
                        'color'   => match ($status) {
                            'Active'    => 'success',
                            'Invited'   => 'warning',
                            'Suspended' => 'error',
                            default     => 'neutral',
                        },
                        'outline' => true,
                        'dot'     => true,
                    ]))->render()),
                ],
            ],
            'rows'                => $tableRows,
            'current_sort_key'    => $orderby,
            'current_sort_dir'    => strtolower($order),
            'sort_url'            => $sortUrl,
            'search_value'        => $search,
            'search_placeholder'  => __('Search members…', 'hukkoo-components'),
            'search_action'       => $baseUrl,
            'add_button'          => ['label' => __('Add member', 'hukkoo-components')],
            'edit_action'         => static fn (array $row): array => ['label' => __('Edit', 'hukkoo-components')],
            'delete_action'       => static fn (array $row): array => [
                'label'   => __('Delete', 'hukkoo-components'),
                'url'     => '#',
                'confirm' => __('Remove this member?', 'hukkoo-components'),
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

        return [
            'title' => __('Team members', 'hukkoo-components'),
            'html'  => $html,
            'code'  => <<<'PHP'
(new ListTable([
    'id'               => 'members',
    'columns'          => [
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'role', 'label' => 'Role', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'format' => fn ($s) => Html::raw(
            (new Badge(['label' => $s, 'color' => 'success', 'outline' => true, 'dot' => true]))->render()
        )],
    ],
    'rows'             => $members,               // already the current page, from a real SQL query
    'current_sort_key' => $orderby,
    'current_sort_dir' => strtolower($order),
    'sort_url'         => fn ($key) => add_query_arg(['orderby' => $key, 'order' => ...], $baseUrl),
    'search_value'     => $search,
    'add_button'       => ['label' => 'Add member', 'url' => '...'],
    'edit_action'      => fn ($row) => ['label' => 'Edit', 'url' => '...' . $row['_id']],
    'delete_action'    => fn ($row) => ['label' => 'Delete', 'url' => '...', 'confirm' => 'Remove this member?'],
    'bulk_actions'     => ['bulk_delete' => 'Delete'],
    'bulk_action_url'  => admin_url('admin-post.php'),
    'pagination'       => ['current' => $page, 'total_pages' => $totalPages, 'url' => fn ($p) => ...],
    'total_label'      => "{$total} items",
]))->render();

// Every interaction here is a real navigation — search, column sort, and
// pagination are all plain links/GET forms, re-running the host's own
// query. See the component's own docblock.
PHP,
        ];
    }
}
