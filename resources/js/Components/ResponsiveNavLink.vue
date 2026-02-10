<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-400 text-start text-base font-medium text-primary-700 bg-primary-50 focus:outline-none focus:text-primary-800 focus:bg-primary-100 focus:border-primary-700 transition duration-150 ease-in-out dark:border-primary-500 dark:text-primary-400 dark:bg-primary-900/20 dark:focus:text-primary-300 dark:focus:bg-primary-900/30 dark:focus:border-primary-600'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-600 dark:focus:text-gray-300 dark:focus:bg-gray-800 dark:focus:border-gray-600',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
