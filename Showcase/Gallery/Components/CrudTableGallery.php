<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Components\Badge;
use Hukkoo\Components\Data\CrudTable;
use Hukkoo\Components\Html;
use Hukkoo\Components\Showcase\Gallery\ApiReference;
use Hukkoo\Components\Showcase\Gallery\Contracts\GalleryInterface;
use Hukkoo\Components\Showcase\Gallery\GalleryPage;
use Hukkoo\Components\Showcase\Gallery\GallerySection;

defined('ABSPATH') || exit;

/**
 * The "Full example" pattern from the Table gallery (search, sortable
 * columns, an Add button, per-row View/Edit/Delete, pagination) — as an
 * actual reusable component instead of hand-assembled markup. This is
 * also the first real host usage: hukkoo-core's own "All Tables" screen
 * (src/Admin/views/table-list.php) is built on this exact class.
 */
final class CrudTableGallery implements GalleryInterface
{
    private const NAMES    = ['Ava Turner', 'Liam Bennett', 'Maya Novak', 'Noah Nakamura', 'Priya Kapoor', 'Ethan Bekele', 'Zoe Rossi', 'Kai Silva', 'Ines Costa', 'Leo Fischer', 'Sofia Reyes', 'Owen Clark'];
    private const ROLES    = ['Product Designer', 'Backend Engineer', 'Support Lead', 'Marketing Manager', 'Data Analyst', 'Frontend Engineer'];
    private const STATUSES = ['Active', 'Active', 'Active', 'Invited', 'Suspended'];

    public static function slug(): string
    {
        return 'crud-table';
    }

    public static function label(): string
    {
        return __('CRUD Table', 'hukkoo-components');
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
            ApiReference::fromReflection(CrudTable::class)
        );
    }

    /** @return array{title: string, html: string, code: string} */
    private static function example(): array
    {
        $rows = [];
        foreach (self::NAMES as $i => $name) {
            $rows[] = [
                'name'   => $name,
                'role'   => self::ROLES[$i % count(self::ROLES)],
                'email'  => sprintf('%s@company.test', str_replace(' ', '.', strtolower($name))),
                'status' => self::STATUSES[$i % count(self::STATUSES)],
            ];
        }

        $html = (new CrudTable([
            'id'       => 'hkgallery-crud-table',
            'numbered' => true,
            'columns'  => [
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
            'rows'                => $rows,
            'search_placeholder'  => __('Search members…', 'hukkoo-components'),
            'add_button'          => ['label' => __('Add member', 'hukkoo-components'), 'color' => 'primary'],
            'view_action'         => static fn (array $row): array => ['label' => __('View', 'hukkoo-components')],
            'edit_action'         => static fn (array $row): array => ['label' => __('Edit', 'hukkoo-components')],
            'delete_action'       => static fn (array $row): array => [
                /* translators: %s: member name */
                'title'   => sprintf(__('Remove %s?', 'hukkoo-components'), $row['name']),
                'message' => __("This will permanently remove them from the team. This can't be undone.", 'hukkoo-components'),
                'form'    => Html::raw(''),
            ],
        ]))->render();

        return [
            'title' => __('Team members', 'hukkoo-components'),
            'html'  => $html,
            'code'  => <<<'PHP'
(new CrudTable([
    'id'       => 'members',
    'numbered' => true,
    'columns'  => [
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'role', 'label' => 'Role', 'sortable' => true],
        ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'format' => fn ($s) => Html::raw(
            (new Badge(['label' => $s, 'color' => 'success', 'outline' => true, 'dot' => true]))->render()
        )],
    ],
    'rows'          => $members,
    'add_button'    => ['label' => 'Add member', 'color' => 'primary', 'url' => '...'],
    'view_action'   => fn ($row) => ['label' => 'View', 'url' => '...' . $row['id']],
    'edit_action'   => fn ($row) => ['label' => 'Edit', 'url' => '...' . $row['id']],
    'delete_action' => fn ($row) => [
        'title'   => "Remove {$row['name']}?",
        'message' => "This will permanently remove them from the team. This can't be undone.",
        // The delete <form>'s inner HTML — hidden fields + nonce are the
        // host's own responsibility, same as Pagination leaves page-change
        // wiring to the host.
        'form'    => Html::raw('<input type="hidden" name="action" value="delete_member" />' . wp_nonce_field(...)),
    ],
]))->render();

// Search, sortable columns, and pagination all run client-side against
// the rows already rendered — see the component's own docblock.
PHP,
        ];
    }
}
