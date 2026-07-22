<?php

namespace App\Concerns;

use App\Support\SecurityQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait SecurityQuestionValidationRules
{
    /**
     * Get the validation rules used to validate a chosen security question.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function securityQuestionRules(): array
    {
        return ['required', 'string', Rule::in(array_keys(SecurityQuestion::OPTIONS))];
    }

    /**
     * Get the validation rules used to validate a security question answer.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function securityAnswerRules(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
