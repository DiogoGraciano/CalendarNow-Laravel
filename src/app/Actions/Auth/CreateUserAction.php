<?php

namespace App\Actions\Auth;

use App\Models\Tenant;
use App\Models\User;
use App\Traits\PasswordValidationRules;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateUserAction
{
    use AsAction, PasswordValidationRules, WithAttributes;

    /**
     * Validate and create a user.
     *
     * @param  array<string, string>  $input
     */
    public function handle(array $attributes, ?Tenant $tenant = null): User
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();
        $tenant ??= tenant();
        $validatedData['tenant_id'] = $tenant->id;
        $user = User::create($validatedData);
        $tenant->addEmail($validatedData['email']);

        return $user;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => $this->passwordRules(),
            'password_confirmation' => 'nullable|string',
        ];
    }
}
