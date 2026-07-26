<?php

namespace App\Rules;

use App\Models\Passport\Client;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class RedirectUriBelongsToClient implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Passport stores redirect URIs as a comma-separated string in the
        // "redirect" text column. Prefer a DB-agnostic membership check so
        // validation works on both MySQL and SQLite.
        $query = Client::query();

        if (! empty($this->data['client_id'])) {
            $query->where('id', $this->data['client_id']);
        }

        $exists = $query->get(['redirect'])->contains(function (Client $client) use ($value) {
            $redirect = $client->getAttributes()['redirect'] ?? '';

            if (is_array($redirect)) {
                return in_array($value, $redirect, true);
            }

            $uris = array_values(array_filter(array_map('trim', explode(',', (string) $redirect))));

            return in_array($value, $uris, true);
        });

        if (! $exists) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
        }
    }
}
