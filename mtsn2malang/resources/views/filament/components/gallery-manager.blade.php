@php
    /** @var \App\Models\GalleryPost|null $galleryPost */
    /** @var string $statePath — path ke field hidden 'gallery_photos_to_delete' */
@endphp

<div
    x-data="{
        toDelete: [],
        toggleDelete(id) {
            const idx = this.toDelete.indexOf(id);
            if (idx === -1) {
                this.toDelete.push(id);
            } else {
                this.toDelete.splice(idx, 1);
            }
            // Sync ke hidden input Livewire
            $wire.set('{{ $statePath }}', this.toDelete);
        },
        isMarked(id) {
            return this.toDelete.includes(id);
        }
    }"
    class="space-y-3"
>
    @if($galleryPost && $galleryPost->photos->count() > 0)
        {{-- Header info --}}
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $galleryPost->photos->count() }}</span>
                foto tersimpan
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Klik foto untuk tandai hapus, klik lagi untuk batal
            </p>
        </div>

        {{-- Grid foto --}}
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-2">
            @foreach($galleryPost->photos as $photo)
            <div
                class="relative cursor-pointer rounded-xl overflow-hidden aspect-square group select-none"
                :class="isMarked({{ $photo->id }}) ? 'ring-2 ring-red-500 ring-offset-1' : 'ring-1 ring-gray-200 dark:ring-gray-700'"
                @click="toggleDelete({{ $photo->id }})"
                title="Klik untuk tandai hapus"
            >
                {{-- Foto --}}
                <img
                    src="{{ asset('storage/' . $photo->file_path) }}"
                    alt="Foto galeri {{ $loop->iteration }}"
                    class="w-full h-full object-cover transition-all duration-200"
                    :class="isMarked({{ $photo->id }}) ? 'opacity-40 scale-95' : 'group-hover:scale-105'"
                    loading="lazy"
                    draggable="false"
                >

                {{-- Overlay hapus --}}
                <div
                    class="absolute inset-0 flex flex-col items-center justify-center transition-all duration-200"
                    :class="isMarked({{ $photo->id }}) ? 'bg-red-500/20' : 'bg-transparent'"
                >
                    {{-- Icon hapus (muncul saat di-mark) --}}
                    <div
                        class="transition-all duration-200"
                        :class="isMarked({{ $photo->id }}) ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"
                    >
                        <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Icon hover (muncul saat hover, belum di-mark) --}}
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/20 transition-all duration-200"
                        :class="isMarked({{ $photo->id }}) ? 'hidden' : ''"
                    >
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 w-8 h-8 rounded-full bg-white/90 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Badge nomor --}}
                <div
                    class="absolute top-1 left-1 w-5 h-5 rounded-full bg-black/50 flex items-center justify-center transition-opacity duration-200"
                    :class="isMarked({{ $photo->id }}) ? 'opacity-0' : 'opacity-100'"
                >
                    <span class="text-white text-[9px] font-bold">{{ $loop->iteration }}</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Counter hapus --}}
        <div
            x-show="toDelete.length > 0"
            x-transition
            class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm"
        >
            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-red-700 dark:text-red-300">
                <span x-text="toDelete.length"></span> foto akan dihapus saat Anda menyimpan.
                <button type="button" @click="toDelete = []; $wire.set('{{ $statePath }}', [])" class="underline font-semibold ml-1">Batalkan semua</button>
            </span>
        </div>
    @else
        <p class="text-sm text-gray-400 italic">Belum ada foto tersimpan.</p>
    @endif
</div>
