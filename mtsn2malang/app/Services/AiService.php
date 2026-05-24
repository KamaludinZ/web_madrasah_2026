<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiService
{
    private string $provider;
    private ?string $geminiKey;
    private ?string $openaiKey;

    public function __construct()
    {
        $this->provider = config('ai.provider', 'gemini');
        $this->geminiKey = config('ai.gemini_key');
        $this->openaiKey = config('ai.openai_key');
    }

    public function generateSeo(string $title, string $content): array
    {
        $prompt = "Kamu adalah ahli SEO untuk website sekolah Islam di Indonesia. Berdasarkan judul dan konten berikut, buatkan:
1. Meta Title (maks 60 karakter, mengandung kata kunci utama)
2. Meta Description (maks 160 karakter, menarik dan informatif)
3. Slug URL (lowercase, gunakan tanda hubung, tanpa karakter khusus)

Judul: {$title}
Konten: " . Str::limit(strip_tags($content), 500) . "

Balas dalam format JSON:
{\"meta_title\": \"...\", \"meta_description\": \"...\", \"slug\": \"...\"}";

        $response = $this->callAi($prompt);
        return json_decode($response, true) ?? [
            'meta_title' => Str::limit($title, 60),
            'meta_description' => Str::limit(strip_tags($content), 160),
            'slug' => Str::slug($title),
        ];
    }

    public function generateDraft(string $points): array
    {
        $prompt = "Kamu adalah jurnalis profesional untuk website sekolah MTsN 2 Kota Malang. Buatkan artikel berita berdasarkan poin-poin kegiatan berikut:

{$points}

Tulis artikel dengan:
- Bahasa Indonesia yang baik dan benar sesuai standar jurnalistik
- Gaya penulisan formal namun mudah dipahami
- Struktur: Lead (paragraf pembuka), Isi (2-3 paragraf), Penutup
- Panjang: 300-500 kata

Balas dalam format JSON:
{\"content\": \"<p>isi artikel dalam HTML</p>\", \"excerpt\": \"ringkasan 1-2 kalimat\"}";

        $response = $this->callAi($prompt);
        return json_decode($response, true) ?? [
            'content' => '<p>' . $points . '</p>',
            'excerpt' => Str::limit($points, 200),
        ];
    }

    public function fixGrammar(string $content): string
    {
        $prompt = "Kamu adalah editor bahasa Indonesia profesional. Perbaiki tata bahasa, ejaan, dan gaya penulisan teks berikut agar sesuai dengan standar publikasi instansi pendidikan pemerintah. Pertahankan makna aslinya. Kembalikan hanya teks yang sudah diperbaiki dalam format HTML yang sama:

{$content}";

        return $this->callAi($prompt) ?: $content;
    }

    private function callAi(string $prompt): string
    {
        try {
            if ($this->provider === 'gemini' && $this->geminiKey) {
                return $this->callGemini($prompt);
            } elseif ($this->provider === 'openai' && $this->openaiKey) {
                return $this->callOpenAi($prompt);
            }
        } catch (\Exception $e) {
            \Log::error('AI Service Error: ' . $e->getMessage());
        }

        return '';
    }

    private function callGemini(string $prompt): string
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$this->geminiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ],
            ]);

        return $response->json('candidates.0.content.parts.0.text', '');
    }

    private function callOpenAi(string $prompt): string
    {
        $response = Http::withToken($this->openaiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2048,
                'temperature' => 0.7,
            ]);

        return $response->json('choices.0.message.content', '');
    }
}
