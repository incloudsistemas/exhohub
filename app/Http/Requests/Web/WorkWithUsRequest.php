<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class WorkWithUsRequest extends FormRequest
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
            'name'    => 'required|max:255',
            'email'   => 'required|max:255',
            'phone'   => 'required|max:255',
            'subject' => 'nullable|max:255',
            'file'    => 'required|file|mimes:doc,docx,pdf,ppt,pptx|max:5120', // máx 5120 => 5 mb
            'message' => 'nullable|max:65535',
        ];
    }
}
