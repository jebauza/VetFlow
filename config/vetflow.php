<?php

return [
    'permissions' => [
        'admin',

        'role.register',
        'role.list',
        'role.edit',
        'role.delete',

        'veterinary.register',
        'veterinary.list',
        'veterinary.edit',
        'veterinary.delete',
        'veterinary.profile',

        'pet.register',
        'pet.list',
        'pet.edit',
        'pet.delete',
        'pet.profile',

        'staff.register',
        'staff.list',
        'staff.edit',
        'staff.delete',

        'appointment.register',
        'appointment.list',
        'appointment.edit',
        'appointment.delete',

        'payment.show',
        'payment.edit',

        'calendar',

        'vaccionation.register',
        'vaccionation.list',
        'vaccionation.edit',
        'vaccionation.delete',

        'surgeries.register',
        'surgeries.list',
        'surgeries.edit',
        'surgeries.delete',

        'medical_records.show',
        'report_grafics.show',
    ],

    'roles' => [
        [
            'name' => 'Admin',
            'permissions' => [
                'admin'
            ],
        ],
        [
            'name' => 'Vet',
            'permissions' => [],
        ],
        [
            'name' => 'Assistant',
            'permissions' => [],
        ],
        [
            'name' => 'Receptionist',
            'permissions' => [],
        ],
    ],

    'users' => [
        // SUPERADMIN
        [
            'email' => env('USER_SUPERADMIN_EMAIL', null),
            'name' => env('USER_SUPERADMIN_EMAIL', null),
            'password' => env('USER_SUPERADMIN_PASSWORD', null),
            'superadmin' => true,
        ],

        // ADMIN
        [
            'email' => 'admin@example.com',
            'name' => 'admin@example.com',
            'password' => 'K@pT5u#w',
            'permissions' => [],
            'roles' => [
                'Admin',
            ],
        ],
    ],
];
