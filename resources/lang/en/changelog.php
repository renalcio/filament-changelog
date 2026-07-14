<?php

return [
    'navigation' => [
        'page_label' => 'Changelog',
        'resource_label' => 'Manage changelog',
    ],

    'model_label' => 'changelog entry',
    'plural_model_label' => 'changelog entries',

    'types' => [
        'added' => 'Added',
        'changed' => 'Changed',
        'deprecated' => 'Deprecated',
        'removed' => 'Removed',
        'fixed' => 'Fixed',
        'security' => 'Security',
    ],

    'fields' => [
        'project' => 'Project',
        'project_placeholder' => 'e.g. my-app',
        'project_helper' => 'Optional — leave empty when tracking a single project.',
        'version' => 'Version',
        'version_placeholder' => '1.2.0 or Unreleased',
        'released' => 'Released',
        'release_date' => 'Release date',
        'type' => 'Type',
        'description' => 'Description',
        'description_placeholder' => 'Describe the change in one line, e.g. "Added dark mode toggle".',
    ],

    'actions' => [
        'import' => 'Import',
        'export' => 'Export',
        'export_md' => 'Export .md',
        'manage' => 'Manage',
    ],

    'import' => [
        'file_label' => 'CHANGELOG.md file',
        'file_helper' => 'Upload a file, or paste its contents below.',
        'paste_label' => '…or paste markdown',
        'project_helper' => 'Tag imported entries with this project (optional).',
        'replace_label' => 'Replace existing entries first',
    ],

    'notifications' => [
        'nothing_to_import_title' => 'Nothing to import',
        'nothing_to_import_body' => 'Provide a file or paste markdown content.',
        'no_entries_title' => 'No entries found',
        'no_entries_body' => 'The content did not match the Keep a Changelog format.',
        'imported_title' => 'Imported :count entries',
    ],

    'reader' => [
        'unreleased' => 'Unreleased',
        'empty' => 'No changelog entries yet.',
        'search_placeholder' => 'Search the changelog…',
        'all_versions' => 'All versions',
        'no_results' => 'No entries match your search.',
        'loading' => 'Loading more…',
    ],
];
