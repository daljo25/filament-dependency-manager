<?php

// translations for Daljo25/FilamentDependencyManager
return [
    'navigation' => [
        'title' => 'Dependency Manager',
        'label' => 'Dependency Manager',
        'group' => 'Dependency Manager',
    ],

    'composer' => [
        'title'            => 'Composer Dependencies',
        'navigation_label' => 'Composer',
    ],

    'npm' => [
        'title'            => 'NPM Dependencies',
        'navigation_label' => 'NPM',
        'columns' => [
            'type' => 'Type',
        ],
        'actions' => [
            'view_npm' => 'View on NPM',
        ],
        'empty' => [
            'heading'     => 'All up to date 🎉',
            'description' => 'No NPM updates available.',
        ],
    ],

    'table' => [
        'columns' => [
            'package'      => 'Package',
            'installed'    => 'Installed',
            'latest'       => 'Latest',
            'update_type'  => 'Update Type',
            'last_updated' => 'Last Updated',
            'description'  => 'Description',
        ],
        'status' => [
            'minor'      => 'Minor / Patch',
            'major'      => 'Major',
            'up_to_date' => 'Up to date',
        ],
        'actions' => [
            'copy_command' => 'Copy Command',
            'copy_success' => 'Command copied to clipboard!',
            'changelog'    => 'Changelog',
            'refresh'      => 'Refresh',
        ],
        'empty' => [
            'heading'     => 'All up to date 🎉',
            'description' => 'No updates available.',
        ],
    ],
];