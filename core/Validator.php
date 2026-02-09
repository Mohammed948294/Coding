<?php

declare(strict_types=1);

namespace Core;

final class Validator
{
    public static function required(array $input, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                $errors[$field] = 'الحقل مطلوب';
            }
        }
        return $errors;
    }

    public static function sanitize(string $value): string
    {
        return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }
}
