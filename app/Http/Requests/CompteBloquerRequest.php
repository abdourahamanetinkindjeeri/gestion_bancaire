<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteBloquerRequest extends FormRequest
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
            'motif' => 'required|string',
            'duree' => 'required|integer|min:1',
            'unite' => 'required|string|in:jour,jours,semaine,semaines,mois,annee,annees',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'duree.required' => 'La durée de blocage est obligatoire.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'unite.required' => 'L\'unité de durée est obligatoire.',
            'unite.in' => 'L\'unité doit être jour, jours, semaine, semaines, mois, annee ou annees.',
        ];
    }
}
