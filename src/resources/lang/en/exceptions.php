<?php

return [
    'guard_does_not_match-create' => 'The given role or permission should use guard `:expectedGuards` instead of `:givenGuard`.',
    'permission_already_exists-create' => 'A `:permissionName` permission already exists for guard `:guardName`.',
    
    'permission_does_not_exist-create' => 'There is no permission named `:permissionName` for guard `:guardName`.',
    'permission_does_not_exist-with_id' => 'There is no [permission] with id `:permissionId`.',
    
    'role_already_exists-create' => 'A role `:roleName` already exists for guard `:guardName`.',
    
    'role_does_not_exist-named' => 'There is no role named `:roleName`.',
    'role_does_not_exist-withId' => 'There is no role with id `:roleId`.',
    
    'unauthorized_exception-for_roles' => 'User does not have the right roles.',
    'unauthorized_exception-for_roles-display_permission' => 'Necessary roles are :permStr.',

    'unauthorized_exception-for_permissions' => 'User does not have the right permissions.',
    'unauthorized_exception-for_permissions-display_permission' => 'Necessary permissions are :permStr.',

    'unauthorized_exception-not_logged_in' => 'User is not logged in.',
];
