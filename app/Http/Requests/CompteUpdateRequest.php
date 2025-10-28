<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteUpdateRequest extends FormRequest
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
            'type' => 'sometimes|required|string|in:cheque,epargne,courant',
            'solde_initial' => 'sometimes|required|numeric|min:10000',
            'devise' => 'sometimes|required|string',
            'statut' => 'sometimes|required|string|in:actif,inactif,bloque,ferme',
            'metadata' => 'sometimes|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque, epargne ou courant.',
            'solde_initial.required' => 'Le solde initial est obligatoire.',
            'solde_initial.numeric' => 'Le solde initial doit être un nombre.',
            'solde_initial.min' => 'Le solde initial doit être d\'au moins 10 000.',
            'devise.required' => 'La devise est obligatoire.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être actif, inactif, bloque ou ferme.',
            'metadata.array' => 'Les métadonnées doivent être un tableau.',
        ];
    }
}
