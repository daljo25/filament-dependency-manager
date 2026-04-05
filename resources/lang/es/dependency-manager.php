<?php

// translations for Daljo25/FilamentDependencyManager
return [
    'navigation' => [
        'title' => 'Gestor de Dependencias',
        'label' => 'Gestor de Dependencias',
        'group' => 'Gestor de Dependencias',
    ],

    'composer' => [
        'title' => 'Dependencias Composer',
        'navigation_label' => 'Composer',
    ],

    'npm' => [
        'title' => 'Dependencias NPM',
        'navigation_label' => 'NPM',
        'columns' => [
            'type' => 'Tipo',
        ],
        'actions' => [
            'view_npm' => 'Ver en NPM',
        ],
        'empty' => [
            'heading' => 'Todo al día 🎉',
            'description' => 'No hay actualizaciones de NPM disponibles.',
        ],
    ],

    'table' => [
        'columns' => [
            'package' => 'Paquete',
            'installed' => 'Instalado',
            'latest' => 'Última versión',
            'update_type' => 'Tipo de actualización',
            'last_updated' => 'Última actualización',
            'description' => 'Descripción',
        ],
        'status' => [
            'minor' => 'Menor / Parche',
            'major' => 'Mayor',
            'up_to_date' => 'Al día',
        ],
        'actions' => [
            'copy_command' => 'Copiar comando',
            'copy_success' => '¡Comando copiado al portapapeles!',
            'changelog' => 'Changelog',
            'refresh' => 'Actualizar',
        ],
        'empty' => [
            'heading' => 'Todo al día 🎉',
            'description' => 'No hay actualizaciones disponibles.',
        ],
    ],
];
