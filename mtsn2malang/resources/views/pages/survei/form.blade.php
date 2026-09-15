@extends('layouts.app')
@section('title', 'Survei - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold font-heading">Survei</h1>
        <p class="text-green-100 mt-2">Silakan pilih survei yang ingin Anda isi melalui platform eksternal resmi.</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($surveyLinks->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-8 text-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Belum ada survei tersedia</h2>
                <p class="text-gray-600 dark:text-gray-400">Admin belum menambahkan link survei. Silakan cek kembali nanti.</p>
            </div>
        @else
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($surveyLinks as $survey)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $survey->title }}</h2>
                        @if($survey->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $survey->description }}</p>
                        @endif

                        <div class="mt-auto pt-2">
                            <a
                                href="{{ $survey->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-primary inline-flex"
                            >
                                Buka Survei
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
