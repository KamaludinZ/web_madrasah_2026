<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! (bool) Setting::get('subscription_enabled', true)) {
            return back()->with('error', 'Langganan newsletter sedang dinonaktifkan.');
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'source' => ['nullable', Rule::in(['footer', 'news-detail'])],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $subscriber = Subscriber::where('email', $validated['email'])->first();

        if ($subscriber) {
            $subscriber->update([
                'name' => $validated['name'] ?? $subscriber->name,
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            return back()->with('success', 'Email Anda sudah terdaftar. Langganan diaktifkan kembali.');
        }

        Subscriber::create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return back()->with('success', 'Berhasil berlangganan update berita terbaru.');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return response()->view('pages.subscription.unsubscribe-result', [
                'success' => false,
                'message' => 'Token unsubscribe tidak valid.',
            ]);
        }

        if (! $subscriber->is_active) {
            return response()->view('pages.subscription.unsubscribe-result', [
                'success' => true,
                'message' => 'Langganan Anda sudah nonaktif sebelumnya.',
            ]);
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return response()->view('pages.subscription.unsubscribe-result', [
            'success' => true,
            'message' => 'Berhasil berhenti berlangganan newsletter.',
        ]);
    }
}
