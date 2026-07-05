<?php

namespace App\Services\Messaging;

/** Mesaj gonderim sonucu (saglayici bagimsiz). */
readonly class SendResult
{
    public function __construct(
        public bool $success,
        public ?string $providerId = null,
        public ?string $error = null,
    ) {}

    public static function ok(?string $providerId = null): self
    {
        return new self(true, $providerId);
    }

    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }
}
