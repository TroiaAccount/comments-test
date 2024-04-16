<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TextRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = urldecode($value);

        // Define the allowed HTML tags as an array
        $allowedTags = ['a', 'i', 'code', 'strong'];

        // Construct regex pattern to match allowed tags and their content
        $pattern = '/<(?:' . implode('|', $allowedTags) . ')(?:\s[^>]*)?>.*?<\/(?:' . implode('|', $allowedTags) . ')>/';

        // Check if the value contains any allowed tags
        if (preg_match($pattern, $value) === 0) {
            // If no allowed tags found, pass the validation
            return;
        }

        // Check if all opened tags are properly closed
        $openedTags = [];
        if (preg_match_all('/<([a-zA-Z]+)[^>]*>/', $value, $matches)) {
            foreach ($matches[1] as $tag) {
                if (!in_array($tag, $allowedTags)) {
                    $fail("$attribute contains disallowed HTML tags.");
                }
                if (!in_array($tag, $openedTags)) {
                    $openedTags[] = $tag;
                } else {
                    $fail("$attribute contains improperly closed HTML tags.");
                }
            }
        }
    }
}
