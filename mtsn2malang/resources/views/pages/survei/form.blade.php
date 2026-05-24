@extends('layouts.app')
@section('title', 'Survei Kepuasan Masyarakat - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold font-heading">Survei Kepuasan Masyarakat (SKM)</h1>
        <p class="text-green-200 mt-2">Bantu kami meningkatkan kualitas layanan dengan mengisi survei ini</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-2xl mx-auto">
        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-8">
            <form action="{{ route('survei.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="skm">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama (Opsional)</label>
                    <input type="text" name="respondent_name" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Anda sebagai</label>
                    <select name="respondent_type" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                        <option value="siswa">Siswa</option>
                        <option value="orang_tua">Orang Tua/Wali</option>
                        <option value="masyarakat">Masyarakat Umum</option>
                        <option value="guru">Guru/Staff</option>
                    </select>
                </div>

                @php
                $questions = [
                    ['id' => 'q1', 'text' => 'Bagaimana penilaian Anda terhadap kemudahan prosedur pelayanan?'],
                    ['id' => 'q2', 'text' => 'Bagaimana penilaian Anda terhadap kesesuaian persyaratan pelayanan?'],
                    ['id' => 'q3', 'text' => 'Bagaimana penilaian Anda terhadap kejelasan petugas pelayanan?'],
                    ['id' => 'q4', 'text' => 'Bagaimana penilaian Anda terhadap kedisiplinan petugas pelayanan?'],
                    ['id' => 'q5', 'text' => 'Bagaimana penilaian Anda terhadap tanggung jawab petugas pelayanan?'],
                    ['id' => 'q6', 'text' => 'Bagaimana penilaian Anda terhadap kemampuan petugas pelayanan?'],
                    ['id' => 'q7', 'text' => 'Bagaimana penilaian Anda terhadap kecepatan pelayanan?'],
                    ['id' => 'q8', 'text' => 'Bagaimana penilaian Anda terhadap keadilan mendapatkan pelayanan?'],
                    ['id' => 'q9', 'text' => 'Bagaimana penilaian Anda terhadap kesopanan dan keramahan petugas?'],
                ];
                @endphp

                <div class="space-y-6">
                    @foreach($questions as $index => $q)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">{{ $index + 1 }}. {{ $q['text'] }}</p>
                        <div class="flex gap-4">
                            @foreach(['1' => 'Tidak Baik', '2' => 'Kurang Baik', '3' => 'Baik', '4' => 'Sangat Baik'] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $val }}" required class="text-school-green focus:ring-green-200">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="w-full btn-primary justify-center mt-6">
                    Kirim Survei
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
