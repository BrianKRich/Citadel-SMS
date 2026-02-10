import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useTheme() {
    const theme = ref({
        primary_color: '#1B3A6B',   // Navy Blue - Georgia Job Challenge Academy
        secondary_color: '#FFB81C',  // Gold - Georgia Job Challenge Academy
        accent_color: '#C8102E',     // Red - Georgia Job Challenge Academy
        background_color: '#ffffff',
        text_color: '#1f2937',
    });

    const loadTheme = async () => {
        try {
            const response = await axios.get('/api/theme');
            theme.value = response.data;
            applyTheme();
        } catch (error) {
            console.error('Failed to load theme:', error);
            applyTheme(); // Apply default theme
        }
    };

    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        if (result) {
            return `${parseInt(result[1], 16)} ${parseInt(result[2], 16)} ${parseInt(result[3], 16)}`;
        }
        return '99 102 241'; // fallback to indigo-500
    };

    const applyTheme = () => {
        const root = document.documentElement;
        // Set RGB values for Tailwind
        root.style.setProperty('--color-primary-rgb', hexToRgb(theme.value.primary_color));
        root.style.setProperty('--color-secondary-rgb', hexToRgb(theme.value.secondary_color));
        root.style.setProperty('--color-accent-rgb', hexToRgb(theme.value.accent_color));
        // Keep hex values for backward compatibility
        root.style.setProperty('--color-primary', theme.value.primary_color);
        root.style.setProperty('--color-secondary', theme.value.secondary_color);
        root.style.setProperty('--color-accent', theme.value.accent_color);
        root.style.setProperty('--color-background', theme.value.background_color);
        root.style.setProperty('--color-text', theme.value.text_color);
    };

    onMounted(() => {
        loadTheme();
    });

    return {
        theme,
        loadTheme,
        applyTheme,
    };
}
