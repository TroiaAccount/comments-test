<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileUploadRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Проверяем тип файла и его свойства
        if ($value->getClientOriginalExtension() === 'txt') {
            // Для текстовых файлов (TXT) проверяем размер
            if ($value->getSize() > 102400) {
                $fail('The file size should not exceed 100 KB.');
            }
        } elseif (in_array($value->getClientOriginalExtension(), ['jpeg', 'gif', 'png'])) {
            // Для изображений (JPEG, GIF, PNG) проверяем ширину и высоту
            list($width, $height) = getimagesize($value->getPathname());
            if ($width > 320 || $height > 240) {
                $fail('The image dimensions should not exceed 320x240 pixels.');
            }
        } else {
            // Если файл не TXT, JPEG, GIF, PNG - считаем его недопустимым
            $fail('The file type is not supported.');
        }
    }
}
