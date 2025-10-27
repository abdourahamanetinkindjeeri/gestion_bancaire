<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelephoneSenegalRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer le numéro (espaces, tirets, parenthèses, points)
        $cleaned = preg_replace('/[\s\-\(\)\.]/', '', $value);

        // Regex : accepter +221, 221 ou rien et numéro commençant par 6 ou 7 suivi de 7 chiffres
        if (!preg_match('/^(\+221|221)?[67]\d{8}$/', $cleaned)) {
            $fail('Le numéro de téléphone doit être un numéro sénégalais valide, ex : +221771234567 ou 771234567.');
        }
    }
}
