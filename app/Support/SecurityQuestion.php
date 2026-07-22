<?php

namespace App\Support;

use Illuminate\Support\Str;

class SecurityQuestion
{
    /**
     * The fixed set of security questions users may choose from.
     *
     * @var array<string, string>
     */
    public const OPTIONS = [
        'age' => 'What is your age?',
        'childhood_nickname' => 'What was your childhood nickname?',
        'favorite_sport' => 'What is your favorite sport?',
    ];

    /**
     * Normalize an answer so comparisons ignore case and surrounding whitespace.
     */
    public static function normalizeAnswer(string $answer): string
    {
        return Str::of($answer)->trim()->lower()->toString();
    }
}
