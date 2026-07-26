<?php

namespace App\Models\Passport;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return true;
    }

    /**
     * Legacy oauth_clients rows lack a grant_types column. Passport's default
     * inference marks confidential first-party clients as client_credentials,
     * which breaks personal-access auth when user_id === client_id.
     */
    protected function grantTypes(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): array {
                if (isset($value)) {
                    return $this->fromJson($value);
                }

                $types = [];

                if ($this->personal_access_client && $this->confidential()) {
                    $types[] = 'personal_access';
                }

                if ($this->password_client) {
                    $types[] = 'password';
                    $types[] = 'refresh_token';
                }

                $redirect = $this->getAttributes()['redirect'] ?? '';

                if ($redirect !== '' && ! $this->personal_access_client && ! $this->password_client) {
                    $types[] = 'authorization_code';
                    $types[] = 'refresh_token';
                }

                return $types;
            },
        );
    }
}
