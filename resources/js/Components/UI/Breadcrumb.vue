<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
        // Each item: { label: string, href?: string }
        // Items with href render as links; last item (no href) is current page
    },
});
</script>

<template>
    <nav class="mb-6 flex" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-y-1 text-sm">
            <li v-for="(item, index) in items" :key="index" class="flex items-center">
                <svg
                    v-if="index > 0"
                    class="mx-2 h-4 w-4 flex-shrink-0 text-gray-300 dark:text-gray-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    aria-hidden="true"
                >
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                >{{ item.label }}</Link>
                <span v-else class="font-medium text-gray-900 dark:text-gray-100">{{ item.label }}</span>
            </li>
        </ol>
    </nav>
</template>
