@props(['context'])

{{--
    Perlindungan formulir publik dari bot: jebakan (honeypot) tersembunyi +
    kode keamanan berupa gambar angka. Diperiksa oleh App\Support\PublicFormGuard.
--}}
<div class="hidden" aria-hidden="true">
    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
</div>

<div>
    <label for="{{ $context }}_captcha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
        Kode Keamanan <span class="text-red-500">*</span>
    </label>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <x-captcha-image :context="$context" class="justify-start" />
        <div class="flex-1">
            <input id="{{ $context }}_captcha" type="text" name="captcha" required inputmode="numeric" autocomplete="off"
                   maxlength="{{ \App\Support\NumericCaptcha::LENGTH }}"
                   class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all tracking-widest font-mono @error('captcha') border-red-400 @enderror"
                   placeholder="Ketik angka pada gambar">
        </div>
    </div>
</div>
