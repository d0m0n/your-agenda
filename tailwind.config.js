import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Zen Kaku Gothic New"', ...defaultTheme.fontFamily.sans],
                serif: ['"Shippori Mincho"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // 「あなた次第」のブランドトークン。上質な文房具(万年筆・革の
                // 手帳)を思わせる配色。ink=万年筆のインクのような温かい黒褐色、
                // leather=手帳の革表紙を思わせるワイン色(主アクセント・操作要素)、
                // brass=金具・箔押しを思わせる真鍮色(装飾的な差し色)、
                // paper/night=用紙の色とその反転(ダークモードの基調色)。
                ink: {
                    50: '#F2EEE7',
                    100: '#E4DCCD',
                    200: '#C7BAA0',
                    400: '#7D6D53',
                    600: '#3C3222',
                    700: '#2B2318',
                    800: '#201A12',
                    900: '#16110B',
                },
                leather: {
                    50: '#F7EAEA',
                    100: '#EDD2D3',
                    200: '#D9A3A6',
                    300: '#B96E73',
                    400: '#8C4451',
                    500: '#6E2A35',
                    600: '#5A2129',
                    700: '#451A20',
                },
                brass: {
                    100: '#F2E7CE',
                    200: '#E4CE9E',
                    300: '#D2B173',
                    400: '#BE9856',
                    500: '#A47F42',
                    600: '#836434',
                },
                paper: {
                    DEFAULT: '#F3ECDD',
                    50: '#FEFCF7',
                    100: '#FAF6EC',
                    200: '#E7DCC2',
                },
                night: {
                    DEFAULT: '#16110B',
                    50: '#100D08',
                },
            },
            animation: {
                'agenda-reveal': 'agenda-reveal 0.7s ease-out forwards',
            },
            keyframes: {
                'agenda-reveal': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [forms],
};
