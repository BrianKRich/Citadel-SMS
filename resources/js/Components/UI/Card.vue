<script setup>
defineProps({
    padding: {
        type: String,
        default: 'p-6',
    },
    shadow: {
        type: Boolean,
        default: true,
    },
    rounded: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div
        :class="[
            'overflow-hidden bg-white',
            shadow ? 'shadow-sm' : '',
            rounded ? 'sm:rounded-lg' : '',
            padding,
        ]"
    >
        <slot />
    </div>
</template>
