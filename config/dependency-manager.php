<?php

// config for Daljo25/FilamentDependencyManager
return [
    /*
     * Path to the composer binary.
     * Leave null to auto-detect.
     */
    'composer_binary' => env('DEPENDENCY_MANAGER_COMPOSER_BIN', null),

    /*
     * Path to the PHP binary.
     * Leave null to use PHP_BINARY constant (current PHP process).
     */
    'php_binary' => env('DEPENDENCY_MANAGER_PHP_BIN', null),

    /*
     * NPM configuration.
     */
    'npm_client' => env('DEPENDENCY_MANAGER_NPM_CLIENT', 'npm'),
    'npm_binary' => env('DEPENDENCY_MANAGER_NPM_BINARY', null),

    /*
     * Navigation group configuration.
     */
    'navigation' => [
        'group' => null, // e.g. 'Administration' to override the group name
    ],

    /*
     * Composer page specific configuration.
     */
    'composer' => [
        'title' => null,
        'navigation_label' => null,
        'icon' => null, // e.g. 'heroicon-o-code-bracket'
        'sort' => 1,
    ],

    /*
     * NPM page specific configuration.
     */
    'npm' => [
        'title' => null,
        'navigation_label' => null,
        'icon' => null, // e.g. 'heroicon-o-cube'
        'sort' => 2,
    ],
];
