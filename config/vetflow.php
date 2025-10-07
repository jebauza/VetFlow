<?php

return [
    'permissions' => [
        'superadmin',
        'admin',
    ],

    'roles' => [
        [
            'name' => 'admin',
            'permissions' => [
                'admin'
            ],
        ],
        [
            'name' => 'vet',
            'permissions' => [],
        ],
        [
            'name' => 'assistant',
            'permissions' => [],
        ],
        [
            'name' => 'receptionist',
            'permissions' => [],
        ],
    ],

    'users' => [
        // SUPERADMIN
        [
            'email' => env('USER_SUPERADMIN_EMAIL', null),
            'name' => env('USER_SUPERADMIN_EMAIL', null),
            'password' => env('USER_SUPERADMIN_PASSWORD', null),
            'permissions' => [
                'superadmin',
            ],
        ],

        // ADMIN
        [
            'email' => 'admin@example.com',
            'name' => 'admin@example.com',
            'password' => 'K@pT5u#w',
            'permissions' => [],
            'roles' => [
                'admin',
            ],
        ],
    ],
];
