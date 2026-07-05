<?php

namespace App\Support;

use App\Models\Business;

/**
 * Aktif tenant (isletme) erisimi icin merkezi yardimci.
 * IdentifyTenant middleware'i tenant'i container'a baglar;
 * global scope'lar ve controller'lar buradan okur.
 */
class Tenancy
{
    public static function set(Business $business): void
    {
        app()->instance('tenant', $business);
    }

    public static function current(): ?Business
    {
        return app()->bound('tenant') ? app('tenant') : null;
    }

    public static function id(): ?int
    {
        return self::current()?->id;
    }

    public static function check(): bool
    {
        return app()->bound('tenant');
    }

    public static function forget(): void
    {
        if (app()->bound('tenant')) {
            app()->forgetInstance('tenant');
        }
    }
}
