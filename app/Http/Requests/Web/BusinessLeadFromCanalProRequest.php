<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class BusinessLeadFromCanalProRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // originLeadId: Identificador do lead do GrupoZap;
        // originListingId: Identificador do anúncio do GrupoZap;
        // clientListingId: Identificador do anúncio para o anunciante (ListingId);

        return [
            'originLeadId'    => 'required|max:255',
            'originListingId' => 'required|max:255',
            'clientListingId' => 'required|max:255',
            'name'            => 'required|max:255',
            'email'           => 'required|max:255',
            'ddd'             => 'required|max:255',
            'phone'           => 'required|max:255',
            'message'         => 'nullable|max:65535',
        ];
    }
}
