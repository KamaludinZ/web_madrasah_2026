<?php

// Nilai di sini hanya cadangan — pengaturan utama ada di panel admin
// (Pengaturan Website → tab AI & SEO).
return [
    'provider' => env('AI_PROVIDER', 'gemini'),
    'gemini_key' => env('GEMINI_API_KEY'),
    'openai_key' => env('OPENAI_API_KEY'),
    'gemini_model' => env('AI_GEMINI_MODEL', 'gemini-2.5-flash'),
    'openai_model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
];
