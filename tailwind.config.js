import preset from './vendor/filament/filament/tailwind.config.preset'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/filipfonal/filament-log-manager/resources/views/pages/logs.blade.php',
    ],
} 