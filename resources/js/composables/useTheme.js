import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useTheme() {
    const theme = ref({
        primary_color: '#6366f1',
        secondary_color: '#8b5cf6',
        accent_color: '#ec4899',
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

    const applyTheme = () => {
        const root = document.documentElement;
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
