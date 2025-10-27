<?php

namespace App\Http\Requests;

use App\Rules\NciSenegalRule;
use App\Rules\TelephoneSenegalRule;
use Illuminate\Foundation\Http\FormRequest;

class CompteStoreRequest extends FormRequest
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
            'type' => 'required|string|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string',
            'client.titulaire' => 'required|string',
            'client.nci' => ['required', new NciSenegalRule()],
            'client.telephone' => ['required', 'unique:clients,telephone', new TelephoneSenegalRule()],
            'client.email' => 'required|email|unique:clients,email',
            'client.adresse' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque ou epargne.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000.',
            'devise.required' => 'La devise est obligatoire.',
            'client.titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'client.nci.required' => 'Le numéro NCI est obligatoire.',
            'client.telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.email.required' => 'L\'adresse email est obligatoire.',
            'client.email.email' => 'L\'adresse email doit être valide.',
            'client.email.unique' => 'Cette adresse email est déjà utilisée.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
        ];
    }
}
