<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: '⚙️',
    },
    href: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: 'indigo',
    },
});
</script>

<template>
    <Link
        :href="href"
        class="group relative overflow-hidden rounded-lg bg-white p-6 shadow transition hover:shadow-lg"
    >
        <div class="flex items-start space-x-4">
            <div
                :class="[
                    'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg text-2xl',
                    color === 'indigo' ? 'bg-indigo-100' : '',
                    color === 'blue' ? 'bg-blue-100' : '',
                    color === 'green' ? 'bg-green-100' : '',
                    color === 'purple' ? 'bg-purple-100' : '',
                    color === 'pink' ? 'bg-pink-100' : '',
                ]"
            >
                {{ icon }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">
                    {{ title }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ description }}
                </p>
            </div>
            <div class="flex-shrink-0 text-gray-400 group-hover:text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </Link>
</template>
