<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AiService
{
    private string $provider;
    private ?string $geminiKey;
    private ?string $openaiKey;

    public function __construct()
    {
        // Utamakan pengaturan dari panel admin (Pengaturan Website → AI & SEO);
        // .env hanya sebagai cadangan. Sebelumnya hanya .env yang dibaca,
        // sehingga kunci yang diisi admin tidak pernah dipakai.
        $this->provider = (string) Setting::get('ai_provider', config('ai.provider', 'gemini'));
        $this->geminiKey = Setting::getSecret('gemini_api_key') ?: config('ai.gemini_key');
        $this->openaiKey = Setting::getSecret('openai_api_key') ?: config('ai.openai_key');
    }

    public function generateSeo(string $title, string $content): array
    {
        $prompt = "Kamu adalah ahli SEO untuk website sekolah Islam di Indonesia. Berdasarkan judul dan konten berikut, buatkan:
1. Meta Title (maks 60 karakter, mengandung kata kunci utama)
2. Meta Description (maks 160 karakter, menarik dan informatif)
3. Slug URL (lowercase, gunakan tanda hubung, tanpa karakter khusus)

Judul: {$title}
Konten: " . Str::limit(strip_tags($content), 500) . "

Balas HANYA dalam format JSON tanpa teks lain:
{\"meta_title\": \"...\", \"meta_description\": \"...\", \"slug\": \"...\"}";

        $data = $this->decodeJson($this->callAi($prompt));

        return [
            'meta_title' => Str::limit((string) ($data['meta_title'] ?? $title), 60, ''),
            'meta_description' => Str::limit((string) ($data['meta_description'] ?? strip_tags($content)), 160, ''),
            'slug' => Str::slug((string) ($data['slug'] ?? $title)),
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

Balas HANYA dalam format JSON tanpa teks lain:
{\"content\": \"<p>isi artikel dalam HTML</p>\", \"excerpt\": \"ringkasan 1-2 kalimat\"}";

        $data = $this->decodeJson($this->callAi($prompt));

        if (blank($data['content'] ?? null)) {
            throw new RuntimeException('Jawaban AI tidak berisi artikel. Silakan coba lagi.');
        }

        return [
            'content' => (string) $data['content'],
            'excerpt' => Str::limit(strip_tags((string) ($data['excerpt'] ?? '')), 500, ''),
        ];
    }

    public function fixGrammar(string $content): string
    {
        $prompt = "Kamu adalah editor bahasa Indonesia profesional. Perbaiki tata bahasa, ejaan, dan gaya penulisan teks berikut agar sesuai dengan standar publikasi instansi pendidikan pemerintah. Pertahankan makna aslinya. Kembalikan hanya teks yang sudah diperbaiki dalam format HTML yang sama, tanpa penjelasan dan tanpa blok kode:

{$content}";

        return $this->stripCodeFence($this->callAi($prompt));
    }

    /**
     * @throws RuntimeException jika AI belum dikonfigurasi atau gagal merespons —
     *                          supaya tombol AI di form menampilkan pesan gagal,
     *                          bukan "berhasil" dengan hasil kosong/palsu.
     */
    private function callAi(string $prompt): string
    {
        $key = $this->provider === 'openai' ? $this->openaiKey : $this->geminiKey;

        if (blank($key)) {
            throw new RuntimeException('API Key AI belum diisi. Isi di Pengaturan Website → tab AI & SEO.');
        }

        try {
            $text = $this->provider === 'openai'
                ? $this->callOpenAi($prompt)
                : $this->callGemini($prompt);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            throw new RuntimeException('Tidak dapat menghubungi layanan AI. Coba lagi nanti.');
        }

        if (trim($text) === '') {
            throw new RuntimeException('Layanan AI tidak memberikan jawaban. Coba lagi nanti.');
        }

        return $text;
    }

    private function callGemini(string $prompt): string
    {
        $model = config('ai.gemini_model', 'gemini-2.5-flash');

        $response = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $this->geminiKey])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 4096,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini API gagal (HTTP ' . $response->status() . '): ' . ($response->json('error.message') ?? 'tanpa pesan'));
        }

        return (string) $response->json('candidates.0.content.parts.0.text', '');
    }

    private function callOpenAi(string $prompt): string
    {
        $response = Http::timeout(60)
            ->withToken($this->openaiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('ai.openai_model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI API gagal (HTTP ' . $response->status() . '): ' . ($response->json('error.message') ?? 'tanpa pesan'));
        }

        return (string) $response->json('choices.0.message.content', '');
    }

    /** Model sering membungkus jawaban dengan ```json ... ``` — buang pembungkusnya. */
    private function stripCodeFence(string $text): string
    {
        $text = trim($text);

        if (preg_match('/^```[a-zA-Z]*\s*(.*?)\s*```$/s', $text, $matches)) {
            return trim($matches[1]);
        }

        return $text;
    }

    private function decodeJson(string $text): array
    {
        $data = json_decode($this->stripCodeFence($text), true);

        if (! is_array($data)) {
            throw new RuntimeException('Format jawaban AI tidak dikenali. Silakan coba lagi.');
        }

        return $data;
    }
}
