<?php

namespace LaraGram\Luna\Ssr;

class BundleDetector
{
    /**
     * Detect and return the path to the SSR bundle file.
     *
     * @return string|null
     */
    public function detect()
    {
        return collect([
            config('luna.ssr.bundle'),
            base_path('bootstrap/ssr/ssr.js'),
            base_path('bootstrap/ssr/app.js'),
            base_path('bootstrap/ssr/ssr.mjs'),
            base_path('bootstrap/ssr/app.mjs'),
            public_path('js/ssr.js'),
            public_path('js/app.js'),
        ])->filter()->first(function ($path) {
            return file_exists($path);
        });
    }
}
