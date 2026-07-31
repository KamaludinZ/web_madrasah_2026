@extends('layouts.app')

@section('title', 'Status Langganan Newsletter')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="max-w-xl mx-auto bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-8 shadow-sm text-center">
        <div class="mx-auto w-14 h-14 rounded-full flex items-center justify-center {{ $success ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
            @if($success)
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            @else
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @endif
        </div>

        <h1 class="mt-4 text-2xl font-bold font-heading text-gray-900 dark:text-white">
            {{ $success ? 'Berhasil' : 'Gagal' }}
        </h1>

        <p class="mt-2 text-gray-600 dark:text-gray-300">
            {{ $message }}
        </p>

        <a href="{{ route('home') }}" class="inline-flex items-center justify-center mt-6 px-5 py-2.5 rounded-xl bg-school-green hover:bg-green-700 text-white text-sm font-semibold transition-colors">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
