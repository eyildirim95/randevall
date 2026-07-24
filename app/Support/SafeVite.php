<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

/**
 * Vite manifest'te olmayan (veya build alinmamis) entry'ler icin 500 vermez.
 */
class SafeVite
{
    /** @param  array<int, string>|string  $entries */
    public function tags(array|string $entries): HtmlString
    {
        $entries = (array) $entries;
        $manifest = $this->manifest();

        if ($manifest === null) {
            return new HtmlString('');
        }

        $available = array_values(array_filter($entries, fn (string $entry) => isset($manifest[$entry])));

        if ($available === []) {
            return new HtmlString('');
        }

        return new HtmlString(Vite::useManifestFilename('build/manifest.json')->withEntryPoints($available)->toHtml());
    }

    private function manifest(): ?array
    {
        $path = public_path('build/manifest.json');

        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
