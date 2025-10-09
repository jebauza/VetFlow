<?php

return [
    'permissions' => [
        'superadmin',
        'admin',
        'register_role',
        'list_role',
        'edit_role',
        'delete_role',
        'register_veterinary',
        'list_veterinary',
        'edit_veterinary',
        'delete_veterinary',
        'profile_veterinary',
        'register_pet',
        'list_pet',
        'edit_pet',
        'delete_pet',
        'profile_pet',
        'register_staff',
        'list_staff',
        'edit_staff',
        'delete_staff',
        'register_appointment',
        'list_appointment',
        'edit_appointment',
        'delete_appointment',
        'show_payment',
        'edit_payment',
        'calendar',
        'register_vaccionation',
        'list_vaccionation',
        'edit_vaccionation',
        'delete_vaccionation',
        'register_surgeries',
        'list_surgeries',
        'edit_surgeries',
        'delete_surgeries',
        'show_medical_records',
        'show_report_grafics',
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
