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

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: 'rgb(var(--color-primary-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-primary-rgb) / 0.05)',
                    100: 'rgb(var(--color-primary-rgb) / 0.1)',
                    200: 'rgb(var(--color-primary-rgb) / 0.2)',
                    300: 'rgb(var(--color-primary-rgb) / 0.3)',
                    400: 'rgb(var(--color-primary-rgb) / 0.4)',
                    500: 'rgb(var(--color-primary-rgb) / 0.5)',
                    600: 'rgb(var(--color-primary-rgb) / 1)',
                    700: 'rgb(var(--color-primary-rgb) / 0.9)',
                    800: 'rgb(var(--color-primary-rgb) / 0.8)',
                    900: 'rgb(var(--color-primary-rgb) / 0.7)',
                },
                secondary: {
                    DEFAULT: 'rgb(var(--color-secondary-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-secondary-rgb) / 0.05)',
                    100: 'rgb(var(--color-secondary-rgb) / 0.1)',
                    200: 'rgb(var(--color-secondary-rgb) / 0.2)',
                    300: 'rgb(var(--color-secondary-rgb) / 0.3)',
                    400: 'rgb(var(--color-secondary-rgb) / 0.4)',
                    500: 'rgb(var(--color-secondary-rgb) / 0.5)',
                    600: 'rgb(var(--color-secondary-rgb) / 1)',
                    700: 'rgb(var(--color-secondary-rgb) / 0.9)',
                    800: 'rgb(var(--color-secondary-rgb) / 0.8)',
                    900: 'rgb(var(--color-secondary-rgb) / 0.7)',
                },
                accent: {
                    DEFAULT: 'rgb(var(--color-accent-rgb) / <alpha-value>)',
                    50: 'rgb(var(--color-accent-rgb) / 0.05)',
                    100: 'rgb(var(--color-accent-rgb) / 0.1)',
                    200: 'rgb(var(--color-accent-rgb) / 0.2)',
                    300: 'rgb(var(--color-accent-rgb) / 0.3)',
                    400: 'rgb(var(--color-accent-rgb) / 0.4)',
                    500: 'rgb(var(--color-accent-rgb) / 0.5)',
                    600: 'rgb(var(--color-accent-rgb) / 1)',
                    700: 'rgb(var(--color-accent-rgb) / 0.9)',
                    800: 'rgb(var(--color-accent-rgb) / 0.8)',
                    900: 'rgb(var(--color-accent-rgb) / 0.7)',
                },
            },
        },
    },

    plugins: [forms],
};
