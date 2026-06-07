<?php

if (! function_exists('webp_picture')) {
    /**
     * Render elemen <picture> dengan sumber WebP + fallback gambar asli.
     *
     * Jika varian `.webp` (siblings dari path asli) tidak ada di disk, fungsi
     * degrade dengan aman ke <img> biasa — sehingga aman untuk path dinamis
     * (mis. gambar yang diupload admin yang belum punya varian WebP).
     *
     * @param string $relPath Path relatif terhadap folder public (mis. 'assets/images/landing/x.jpg').
     * @param array<string,string|int> $attrs Atribut untuk tag <img> (mis. ['alt'=>'..','class'=>'..','loading'=>'lazy']).
     */
    function webp_picture(string $relPath, array $attrs = []): string
    {
        $relPath = ltrim($relPath, '/');
        $imgUrl  = base_url($relPath);

        $attrStr = '';
        foreach ($attrs as $key => $value) {
            $attrStr .= ' ' . $key . '="' . esc((string) $value, 'attr') . '"';
        }

        $imgTag = '<img src="' . esc($imgUrl, 'attr') . '"' . $attrStr . '>';

        $webpRel = preg_replace('/\.(jpe?g|png)$/i', '.webp', $relPath);
        if ($webpRel !== null && $webpRel !== $relPath) {
            $webpFs = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $webpRel);
            if (is_file($webpFs)) {
                return '<picture><source srcset="' . esc(base_url($webpRel), 'attr') . '" type="image/webp">' . $imgTag . '</picture>';
            }
        }

        return $imgTag;
    }
}
