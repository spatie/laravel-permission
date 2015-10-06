<?php

return [
    'tables' => [
        'users' => env('ACL_USERS','users'),
        'roles' => env('ACL_ROLES','roles'),
        'permissions' => env('ACL_PERMISSIONS','permissions'),
        'role_has_permissions' => env('ACL_ROLE_HAS_PERMISSIONS','role_has_permissions'),
        'user_has_roles' => env('ACL_USER_HAS_ROLES','user_has_roles'),
        'user_has_permissions' => env('ACL_USER_HAS_PERMISSIONS','user_has_permissions'),
    ]
];


