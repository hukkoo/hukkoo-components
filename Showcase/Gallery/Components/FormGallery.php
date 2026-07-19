<?php

namespace Hukkoo\Components\Showcase\Gallery\Components;

use Hukkoo\Components\Forms\Checkbox;
use Hukkoo\Components\Forms\Color;
use Hukkoo\Components\Forms\Date;
use Hukkoo\Components\Forms\DateTime;
use Hukkoo\Components\Forms\FileUpload;
use Hukkoo\Components\Forms\Form;
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

defined('ABSPATH') || exit;

/**
 * Form is the odd one out among the Forms/*.php classes: every other
 * class in that namespace is a single Field (Text, Select, Checkbox, …)
 * already covered by the Input gallery. Form is the wrapper that
 * assembles a list of Field instances into one real <form> with a nonce
 * and submit button, so it gets its own page instead of another "Input"
 * section.
 */
final class FormGallery implements GalleryInterface
{
    public static function slug(): string
    {
        return 'form';
    }

    public static function label(): string
    {
        return __('Form', 'hukkoo-components');
    }

    public static function render(): string
    {
        return GalleryPage::render(
            self::label(),
            [
                GallerySection::render(__('All field types, two per row', 'hukkoo-components'), [
                    self::all_fields_example(),
                ]),
                GallerySection::render(__('With validation errors', 'hukkoo-components'), [
                    self::validation_example(),
                ]),
            ],
            ApiReference::fromReflection(Form::class)
        );
    }

    /**
     * One of every Forms/*.php field type, laid out via Form's
     * 'layout' => 'grid' — two fields per row, with the wide ones
     * (Textarea, Checkbox, Radio, the submit button) spanning both
     * columns via 'width' => 'full'.
     *
     * @return array{title: string, html: string, code: string}
     */
    private static function all_fields_example(): array
    {
        $form = (new Form([
            'layout' => 'grid',
            'fields' => [
                new Text(['name' => 'hkgallery-form-first', 'label' => __('First name', 'hukkoo-components'), 'required' => true]),
                new Text(['name' => 'hkgallery-form-last', 'label' => __('Last name', 'hukkoo-components'), 'required' => true]),
                new Text(['name' => 'hkgallery-form-email', 'label' => __('Email', 'hukkoo-components'), 'type' => 'email', 'required' => true]),
                new Text(['name' => 'hkgallery-form-phone', 'label' => __('Phone', 'hukkoo-components'), 'type' => 'tel']),
                new Number(['name' => 'hkgallery-form-age', 'label' => __('Age', 'hukkoo-components'), 'min' => '0', 'max' => '120']),
                new Color(['name' => 'hkgallery-form-color', 'label' => __('Favorite color', 'hukkoo-components')]),
                new Select([
                    'name'        => 'hkgallery-form-role',
                    'label'       => __('Role', 'hukkoo-components'),
                    'placeholder' => __('Select a role', 'hukkoo-components'),
                    'options'     => ['designer' => 'Designer', 'engineer' => 'Engineer', 'manager' => 'Manager'],
                ]),
                new Lookup([
                    'name'        => 'hkgallery-form-manager',
                    'label'       => __('Manager', 'hukkoo-components'),
                    'placeholder' => __('Search managers…', 'hukkoo-components'),
                    'options'     => ['ava' => 'Ava Turner', 'liam' => 'Liam Bennett', 'maya' => 'Maya Novak'],
                ]),
                new Date(['name' => 'hkgallery-form-start', 'label' => __('Start date', 'hukkoo-components')]),
                new Time(['name' => 'hkgallery-form-time', 'label' => __('Preferred time', 'hukkoo-components')]),
                new DateTime(['name' => 'hkgallery-form-meeting', 'label' => __('Meeting time', 'hukkoo-components'), 'width' => 'full']),
                new FileUpload(['name' => 'hkgallery-form-resume', 'label' => __('Resume', 'hukkoo-components'), 'accept' => '.pdf,.doc,.docx', 'width' => 'full']),
                new Textarea([
                    'name'        => 'hkgallery-form-bio',
                    'label'       => __('Bio', 'hukkoo-components'),
                    'placeholder' => __('A short introduction…', 'hukkoo-components'),
                    'width'       => 'full',
                ]),
                new Radio([
                    'name'    => 'hkgallery-form-contact',
                    'label'   => __('Preferred contact method', 'hukkoo-components'),
                    'options' => ['email' => 'Email', 'phone' => 'Phone', 'text' => 'Text message'],
                    'value'   => 'email',
                    'width'   => 'full',
                ]),
                new Checkbox([
                    'name'  => 'hkgallery-form-subscribe',
                    'label' => __('Subscribe to the newsletter', 'hukkoo-components'),
                    'width' => 'full',
                ]),
            ],
            'submit_label' => __('Create account', 'hukkoo-components'),
        ]))->render();

        return [
            'title' => __('Every field type', 'hukkoo-components'),
            'html'  => $form,
            'code'  => <<<'PHP'
(new Form([
    // Two fields per row; a field opts into spanning both with
    // 'width' => 'full' (Textarea, Checkbox, Radio and the submit
    // button below all do this automatically or explicitly).
    'layout' => 'grid',
    'fields' => [
        new Text(['name' => 'first', 'label' => 'First name', 'required' => true]),
        new Text(['name' => 'last', 'label' => 'Last name', 'required' => true]),
        new Text(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true]),
        new Text(['name' => 'phone', 'label' => 'Phone', 'type' => 'tel']),
        new Number(['name' => 'age', 'label' => 'Age', 'min' => '0', 'max' => '120']),
        new Color(['name' => 'color', 'label' => 'Favorite color']),
        new Select(['name' => 'role', 'label' => 'Role', 'options' => [...]]),
        new Lookup(['name' => 'manager', 'label' => 'Manager', 'options' => [...]]),
        new Date(['name' => 'start', 'label' => 'Start date']),
        new Time(['name' => 'time', 'label' => 'Preferred time']),
        new DateTime(['name' => 'meeting', 'label' => 'Meeting time', 'width' => 'full']),
        new FileUpload(['name' => 'resume', 'label' => 'Resume', 'width' => 'full']),
        new Textarea(['name' => 'bio', 'label' => 'Bio', 'width' => 'full']),
        new Radio(['name' => 'contact', 'label' => 'Preferred contact method', 'options' => [...], 'width' => 'full']),
        new Checkbox(['name' => 'subscribe', 'label' => 'Subscribe to the newsletter', 'width' => 'full']),
    ],
    'submit_label' => 'Create account',
]))->render();
PHP,
        ];
    }

    /** @return array{title: string, html: string, code: string} */
    private static function validation_example(): array
    {
        $form = (new Form([
            // Same 'grid' layout as the section above — otherwise the
            // card (sized to match the grid form next to it) leaves the
            // rest of its width empty around a single narrow column.
            // Two fields also happen to fill exactly one grid row, so
            // nothing here needs 'width' => 'full'.
            'layout' => 'grid',
            'fields' => [
                new Text([
                    'name'     => 'hkgallery-form-v-name',
                    'label'    => __('Name', 'hukkoo-components'),
                    'required' => true,
                ]),
                new Text([
                    'name'     => 'hkgallery-form-v-email',
                    'label'    => __('Email', 'hukkoo-components'),
                    'type'     => 'email',
                    'value'    => 'not-an-email',
                    'required' => true,
                    'error'    => __('Enter a valid email address.', 'hukkoo-components'),
                ]),
            ],
            'submit_label' => __('Save', 'hukkoo-components'),
        ]))->render();

        return [
            'title' => __('Field errors', 'hukkoo-components'),
            'html'  => $form,
            // Field's own 'error' arg renders the message and the invalid
            // styling — Form itself has no error-handling logic of its
            // own to demonstrate beyond passing already-validated Field
            // instances through.
            'code'  => <<<'PHP'
(new Form([
    'layout' => 'grid',
    'fields' => [
        new Text(['name' => 'name', 'label' => 'Name', 'required' => true]),
        new Text([
            'name'  => 'email',
            'label' => 'Email',
            'type'  => 'email',
            'value' => 'not-an-email',
            'error' => 'Enter a valid email address.',
        ]),
    ],
    'submit_label' => 'Save',
]))->render();
PHP,
        ];
    }
}
