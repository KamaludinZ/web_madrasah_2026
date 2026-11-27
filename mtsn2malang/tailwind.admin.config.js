/**
 * Kelas utilitas Tailwind untuk tampilan KUSTOM panel admin (Filament 5).
 *
 * Sejak Filament 4, CSS bawaan panel tidak lagi menyertakan kelas utilitas
 * umum (flex, rounded-lg, text-gray-500, ...) yang dipakai halaman kustom
 * seperti Pengaturan, Profil, Login, Maintenance, Portal & Migrasi WordPress.
 * File ini membangun HANYA kelas yang benar-benar dipakai di file admin,
 * tanpa preflight (reset CSS) agar tidak mengganggu gaya bawaan Filament.
 * Dimuat di panel lewat render hook (AdminPanelProvider).
 */

// Warna mengikuti palet panel Filament (CSS variable --primary-600, dst.),
// termasuk dukungan transparansi seperti bg-gray-800/50.
const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const palette = (name) =>
    Object.fromEntries(
        shades.map((shade) => [
            shade,
            ({ opacityValue }) =>
                opacityValue === undefined || opacityValue === '1'
                    ? `var(--${name}-${shade})`
                    : `color-mix(in oklab, var(--${name}-${shade}) calc(${opacityValue} * 100%), transparent)`,
        ]),
    );

export default {
    darkMode: 'class',
    content: [
        './resources/views/filament/**/*.blade.php',
        './resources/views/components/captcha-image.blade.php',
        './app/Filament/**/*.php',
        './app/Providers/Filament/**/*.php',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                primary: palette('primary'),
                gray: palette('gray'),
                danger: palette('danger'),
                success: palette('success'),
                warning: palette('warning'),
                info: palette('info'),
                school: {
                    green: '#1a7a3c',
                    'green-light': '#2ea84f',
                    'green-dark': '#0f5a2a',
                    orange: '#f97316',
                },
            },
        },
    },
};
