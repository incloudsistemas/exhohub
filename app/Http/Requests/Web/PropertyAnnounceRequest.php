<?php

namespace App\Http\Requests\Web;

use App\Rules\FloatPtBrFormatRule;
use Illuminate\Foundation\Http\FormRequest;

class PropertyAnnounceRequest extends FormRequest
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
        return [
            'name'        => 'required|max:255',
            'email'       => 'required|max:255',
            'phone'       => 'required|max:255',
            'role'        => 'required|max:255',
            'type'        => 'required|max:255',
            'bedroom'     => 'required|max:255',
            'suite'       => 'nullable|max:255',
            'bathroom'    => 'required|max:255',
            'garage'      => 'required|max:255',
            'useful_area' => ['required', new FloatPtBrFormatRule],
            'total_area'  => ['nullable', new FloatPtBrFormatRule],
            'sale_price'  => ['nullable', new FloatPtBrFormatRule],
            'rent_price'  => ['nullable', new FloatPtBrFormatRule],
            'tax_price'   => ['nullable', new FloatPtBrFormatRule],
            'condo_price' => ['nullable', new FloatPtBrFormatRule],

            'address.zipcode'      => 'required|max:255',
            'address.uf'           => 'required|max:255',
            'address.city'         => 'required|max:255',
            'address.district'     => 'required|max:255',
            'address.address_line' => 'required|max:255',
            'address.number'       => 'nullable|max:255',
            'address.complement'   => 'nullable|max:255',
        ];
    }
}
