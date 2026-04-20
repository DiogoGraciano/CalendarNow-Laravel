<?php

namespace App\Actions\Fortify;

use App\Actions\Auth\CreateUserAction;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\PasswordValidationRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Stancl\Tenancy\Database\Models\Domain;

class CreateNewTenantAndUserAction implements CreatesNewUsers
{
    use AsAction, PasswordValidationRules, WithAttributes;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $attributes): User
    {
        $this->fill($attributes);

        $validatedData = $this->validateAttributes();

        $tenantData = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'plan_id' => Plan::where('is_default', true)->first()?->id ?? Plan::first()?->id,
        ];

        DB::beginTransaction();
        try {
            $tenant = Tenant::create($tenantData);
            $subdomain = $this->generateUniqueSubdomain($validatedData['name']);
            $baseDomain = $this->getBaseDomain();
            $domain = $subdomain.'.'.$baseDomain;

            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $user = CreateUserAction::run($validatedData, $tenant);

            session(['tenant_domain' => $domain]);

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

    /**
     * Gera um subdomínio único baseado no nome do usuário.
     */
    private function generateUniqueSubdomain(string $name): string
    {
        $baseSubdomain = Str::slug($name);
        $subdomain = $baseSubdomain;
        $counter = 1;
        $baseDomain = $this->getBaseDomain();

        while (Domain::where('domain', $subdomain.'.'.$baseDomain)->exists()) {
            $subdomain = $baseSubdomain.'-'.$counter;
            $counter++;
        }

        return $subdomain;
    }

    /**
     * Obtém o domínio base a partir da configuração.
     */
    private function getBaseDomain(): string
    {
        $centralDomains = config('tenancy.central_domains', ['localhost']);
        $baseDomain = $centralDomains[0] ?? 'localhost';

        if ($baseDomain === 'localhost') {
            return 'localhost';
        }

        $parts = explode('.', $baseDomain);
        if (count($parts) >= 2) {
            return $parts[count($parts) - 2].'.'.$parts[count($parts) - 1];
        }

        return $baseDomain;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => $this->passwordRules(),
            'password_confirmation' => 'required|string',
            'accept_terms' => 'required|in:1',
        ];
    }
}
