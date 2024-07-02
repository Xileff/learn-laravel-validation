<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== strtoupper($value)) {
            $fail("The $attribute must be uppercase");
        }

        // if ($value !== strtoupper($value)) {
        //     // baca dari file validation.php
        //     $fail('validation.custom.uppercase')->translate([
        //         'attribute' => $attribute,
        //         'value' => $value,
        //     ]);
        // }
    }
}
