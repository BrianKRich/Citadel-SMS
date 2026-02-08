<script setup>
defineProps({
    primaryColor: {
        type: String,
        required: true,
    },
    secondaryColor: {
        type: String,
        required: true,
    },
    accentColor: {
        type: String,
        required: true,
    },
    backgroundColor: {
        type: String,
        required: true,
    },
    textColor: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div
        class="rounded-lg border-2 border-gray-200 p-8 transition-all"
        :style="{ backgroundColor: backgroundColor, color: textColor }"
    >
        <h4 class="mb-4 text-2xl font-bold">Sample Content</h4>
        <p class="mb-6">
            This is how your text will look with the selected colors. The preview updates in real-time as you adjust the color values.
        </p>

        <div class="space-y-3">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90"
                    :style="{ backgroundColor: primaryColor }"
                >
                    Primary Button
                </button>

                <button
                    type="button"
                    class="rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90"
                    :style="{ backgroundColor: secondaryColor }"
                >
                    Secondary Button
                </button>

                <button
                    type="button"
                    class="rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90"
                    :style="{ backgroundColor: accentColor }"
                >
                    Accent Button
                </button>
            </div>

            <div class="mt-6 rounded-md p-4" :style="{ backgroundColor: primaryColor + '20' }">
                <p class="text-sm">
                    This is a highlighted section using the primary color with transparency.
                </p>
            </div>
        </div>
    </div>
</template>
