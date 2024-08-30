import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                atechBlue: {
                    DEFAULT: '#f4f1eb',
                    light: '#B8CFD6',
                    dark: '#B8CFD6',
                },
                atechGreen: {
                    DEFAULT: '#2ECC71',
                    light: '#3DFF88',
                    dark: '#27AE60',
                },
                atechBlush: {
                    DEFAULT: '#E74C3C',
                    light: '#cd9fa2',
                    dark: '#C0392B',
                },
                atechPeach: {
                    DEFAULT: '#F39C12',
                    light: '#e7cdb3',
                    dark: '#D68910',
                },
            }
        },
    },

    plugins: [forms],
};
