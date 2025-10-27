<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NciSenegalRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format NCI Sénégal : 13 chiffres commençant par 1 ou 2
        if (!preg_match('/^[12]\d{12}$/', $value)) {
            $fail('Le numéro NCI doit être composé de 13 chiffres et commencer par 1 ou 2.');
        }
    }
}
