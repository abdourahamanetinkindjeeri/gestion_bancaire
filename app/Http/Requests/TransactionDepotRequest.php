<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionDepotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|uuid|exists:comptes,id',
            'montant' => 'required|numeric|min:1000',
            'devise' => 'required|string|max:10',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'compte_id.required' => 'L\'identifiant du compte est obligatoire.',
            'compte_id.uuid' => 'L\'identifiant du compte doit être un UUID valide.',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 1000.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.string' => 'La devise doit être une chaîne de caractères.',
            'devise.max' => 'La devise ne peut pas dépasser 10 caractères.',
        ];
    }
}
