<?php

namespace Spatie\Permission;

use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ClientService
{
    /**
     * @return Client
     */
    public static function getClient(string $bearerToken)
    {
        $tokenId = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText('empty', 'empty'))
            ->parser()
            ->parse($bearerToken)
            ->claims()
            ->get('jti');

        $client = Token::find($tokenId)->client;
        $client->loadMissing(['permissions', 'roles.permissions']);

        if (! method_exists($client, 'hasAnyRole') || ! method_exists($client, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($client);
        }

        return $client;
    }

    /**
     * @param  Client  $client
     */
    public static function getClientPermissions($client): array
    {
        $permissions = $client->permissions->pluck('name')->toArray();
        foreach ($client->roles as $role) {
            $permissions = array_merge($permissions, $role->permissions->pluck('name')->toArray());
        }

        return $permissions;
    }

    /**
     * @param  Client  $client
     */
    public static function getClientRoles($client): array
    {
        return $client->roles->pluck('name')->toArray();
    }
}
