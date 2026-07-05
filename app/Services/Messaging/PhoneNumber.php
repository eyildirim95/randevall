<?php

namespace App\Services\Messaging;

/** Telefon numarasi normalizasyonu (E.164). */
class PhoneNumber
{
    /**
     * KKTC/Turkiye odakli E.164 donusumu:
     *  05xx... → +905xx..., 0533... (KKTC) ayni sekilde +90 ile baslar.
     */
    public static function e164(string $phone, string $defaultCountry = '90'): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone) ?? '';

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '00')) {
            return '+'.substr($phone, 2);
        }

        if (str_starts_with($phone, '0')) {
            return '+'.$defaultCountry.substr($phone, 1);
        }

        if (str_starts_with($phone, $defaultCountry)) {
            return '+'.$phone;
        }

        return '+'.$defaultCountry.$phone;
    }
}
