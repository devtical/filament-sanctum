<?php

return [

    'navigation' => [
        'should_register' => true,
        'sort' => -1,
        'group' => null,
    ],

    'abilities' => [
        'users:read' => 'Read User',
        'users:create' => 'Create User',
        'users:update' => 'Update User',
        'users:delete' => 'Delete User',
        'blog:read' => 'Read Blog',
        'blog:create' => 'Create Blog',
        'blog:update' => 'Update Blog',
        'blog:delete' => 'Delete Blog',
    ],

    'columns' => 4,
    'navigation_menu' => true,
    'user_menu' => false,
    'label' => 'Sanctum',
    'slug' => 'sanctum',

];
