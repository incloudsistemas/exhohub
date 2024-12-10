<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class BusinessLeadFromMetaAdsRequest extends FormRequest
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
            'account_id'  => 'required|max:255',
            'campaign_id' => 'required|max:255',
            'name'        => 'required|max:255',
            'email'       => 'required|max:255',
            'phone'       => 'required|max:255',
            'message'     => 'nullable|max:65535',
        ];
    }
}
