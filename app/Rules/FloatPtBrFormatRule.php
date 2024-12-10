<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FloatPtBrFormatRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = str_replace(',', '.', str_replace('.', '', $value));

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {

            $fail('The :attribute must be a valid number.');
        }
    }
}
