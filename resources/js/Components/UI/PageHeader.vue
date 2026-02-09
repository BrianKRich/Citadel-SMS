<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="mb-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
            {{ title }}
        </h3>
        <p v-if="description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ description }}
        </p>

        <!-- Optional slot for action buttons -->
        <div v-if="$slots.actions" class="mt-3">
            <slot name="actions" />
        </div>
    </div>
</template>
