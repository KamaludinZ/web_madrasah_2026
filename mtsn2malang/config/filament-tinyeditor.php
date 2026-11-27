<?php

return [
    'version' => [
        // Selalu sama dengan versi paket tinymce/tinymce di composer.lock
        // (dipakai untuk URL CDN bila provider = cloud).
        'tiny' => \Composer\InstalledVersions::getPrettyVersion('tinymce/tinymce') ?? '8.11.0',
        'language' => [
            // https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/
            'version' => '25.8.4',
            'package' => 'langs8',
        ],
        'licence_key' => env('TINY_LICENSE_KEY', 'no-api-key'),
    ],
    // vendor = TinyMCE dimuat dari server sendiri (public/vendor/tinymce,
    // versinya ikut composer.lock dan ikut dipindai keamanannya) — tanpa
    // bergantung pada CDN pihak ketiga. Isi TINYMCE_PROVIDER=cloud untuk
    // memakai Tiny Cloud (butuh TINY_LICENSE_KEY).
    'provider' => env('TINYMCE_PROVIDER', 'vendor'),
    // 'direction' => 'rtl',
    /**
     * change darkMode: 'auto'|'force'|'class'|'media'|false|'custom'
     */
    'darkMode' => 'auto',

    /** cutsom */
    'skins' => [
        // oxide, oxide-dark, tinymce-5, tinymce-5-dark
        'ui' => 'oxide',

        // dark, default, document, tinymce-5, tinymce-5-dark, writer
        'content' => 'default'
    ],

    // Plugin "media" aman dipakai sejak TinyMCE 8.9.3 (XSS data-mce-object,
    // GHSA-mf2p-h6hf-fcwm). Versi minimum dijaga DependencySecurityTest.
    'profiles' => [
        'default' => [
            'plugins' => 'accordion autoresize codesample directionality advlist link image lists preview pagebreak searchreplace wordcount code fullscreen insertdatetime media table emoticons',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignleft aligncenter alignright | numlist bullist outdent indent | forecolor backcolor | blockquote table toc hr | image link media codesample emoticons | wordcount fullscreen',
            'upload_directory' => null,
            'custom_configs' => ['language_url' => '/js/amidesfahani/filament-tinyeditor/tinymce-lang-id.js'],
        ],

        'simple' => [
            'plugins' => 'autoresize directionality emoticons link wordcount',
            'toolbar' => 'removeformat | bold italic | rtl ltr | numlist bullist | link emoticons',
            'upload_directory' => null,
            'custom_configs' => ['language_url' => '/js/amidesfahani/filament-tinyeditor/tinymce-lang-id.js'],
        ],

        'minimal' => [
            'plugins' => 'link wordcount',
            'toolbar' => 'bold italic link numlist bullist',
            'upload_directory' => null,
            'custom_configs' => ['language_url' => '/js/amidesfahani/filament-tinyeditor/tinymce-lang-id.js'],
        ],

        'full' => [
            'plugins' => 'accordion autoresize codesample directionality advlist autolink link image lists charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist outdent indent accordion | forecolor backcolor | blockquote table toc hr | image link anchor media codesample emoticons | visualblocks print preview wordcount fullscreen help',
            'upload_directory' => null,
            'custom_configs' => ['language_url' => '/js/amidesfahani/filament-tinyeditor/tinymce-lang-id.js'],
        ],
    ],

    /**
     * this option will load optional language file based on you app locale
     * example:
     * languages => [
     *      'fa' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/fa.min.js',
     *      'es' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.7.29/langs7/es.min.js',
     *      'ja' => asset('assets/ja.min.js')
     * ]
     */
    // Bahasa Indonesia disimpan di server sendiri (tinymce-i18n 25.8.4,
    // lisensi MIT) — editor tidak gagal dimuat bila CDN jsDelivr terganggu.
    // `language_url` tiap profil (custom_configs) menunjuk file yang sama
    // agar TinyMCE tidak mengambil bahasa dari CDN.
    'languages' => [
        'id' => resource_path('js/vendor/tinymce-i18n/id.js'),
    ],

    // Samakan tampilan di dalam editor dengan tampilan publik (.prose prose-lg
    // di resources/views/pages/berita/show.blade.php), supaya WYSIWYG-nya jujur.
    'extra' => [
        'content_style' => 'body { font-family: "Plus Jakarta Sans", Inter, sans-serif; font-size: 1.05rem; line-height: 1.75; color: #1f2937; max-width: 100%; } h1,h2,h3,h4,h5,h6 { font-family: "Poppins", sans-serif; font-weight: 700; } a { color: #1a7a3c; } img { border-radius: 0.75rem; max-width: 100%; height: auto; } blockquote { border-left: 4px solid #1a7a3c; padding-left: 1rem; color: #4b5563; font-style: italic; }',
    ]
];
