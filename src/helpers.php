<?php

if (! function_exists('getModelForGuard')) {
    /**
     * @return string|null
     */
    function getModelForGuard(string $guard)
    {
        // Get the guard configuration
        $guardConfig = config("auth.guards.{$guard}");
        
        // If the guard has a provider and the provider is defined
        if (isset($guardConfig['provider'])) {
            $provider = $guardConfig['provider'];
            
            // Check if the provider uses LDAP
            $providerConfig = config("auth.providers.{$provider}");
    
            if (isset($providerConfig['driver']) && $providerConfig['driver'] === 'ldap') {
                // Return the Eloquent model defined in the LDAP provider's database configuration
                return $providerConfig['database']['model'] ?? null;
            }
    
            // Otherwise, return the standard Eloquent model
            return config("auth.providers.{$provider}.model");
        }
    
        return null;
    }

}

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param  int|string|null|\Illuminate\Database\Eloquent\Model  $id
     */
    function setPermissionsTeamId($id)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    /**
     * @return int|string|null
     */
    function getPermissionsTeamId()
    {
        return app(\Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
