<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation Settings
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'slug' => 'sanctum',
        'icon' => 'heroicon-o-finger-print',

        'sidebar_menu' => [
            'enabled' => false,
            'sort' => -1,
            'group' => null,
        ],

        'user_menu' => [
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Creation Settings
    |--------------------------------------------------------------------------
    */
    'abilities' => [
        'columns' => 4,
        'list' => [
            // User management abilities
            'users:read' => 'Read User',
            'users:create' => 'Create User',
            'users:update' => 'Update User',
            'users:delete' => 'Delete User',

            // Blog management abilities
            'blog:read' => 'Read Blog',
            'blog:create' => 'Create Blog',
            'blog:update' => 'Update Blog',
            'blog:delete' => 'Delete Blog',

            // Add more abilities as needed for your application
            // Format: 'ability:action' => 'Human Readable Description'
        ],
    ],

];
