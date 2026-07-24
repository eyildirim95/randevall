<?php

namespace App\Support;

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

        $html = [];

        foreach ($entries as $entry) {
            $file = $manifest[$entry]['file'] ?? null;

            if (! is_string($file) || $file === '') {
                continue;
            }

            $html[] = sprintf(
                '<script type="module" src="%s"></script>',
                e(asset('build/'.$file))
            );
        }

        return new HtmlString(implode("\n", $html));
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
