<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

class ContrasenaSuperAdmin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regla = Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        $validator = validator(
            [$attribute => $value],
            [$attribute => ['required', 'string', $regla]]
        );

        if ($validator->fails()) {
            $fail('La contraseña debe tener al menos 12 caracteres con mayúsculas, minúsculas, números y símbolos.');
        }
    }
}
