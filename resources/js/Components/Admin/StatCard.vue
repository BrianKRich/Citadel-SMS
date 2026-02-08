<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: '',
    },
    trend: {
        type: String,
        default: '', // 'up', 'down', or ''
    },
    trendValue: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div v-if="icon" class="text-3xl" v-html="icon"></div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="truncate text-sm font-medium text-gray-500">
                        {{ title }}
                    </dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ value }}
                        </div>
                        <div
                            v-if="trendValue"
                            :class="[
                                'ml-2 flex items-baseline text-sm font-semibold',
                                trend === 'up' ? 'text-green-600' : trend === 'down' ? 'text-red-600' : 'text-gray-500',
                            ]"
                        >
                            <span v-if="trend === 'up'">↑</span>
                            <span v-if="trend === 'down'">↓</span>
                            {{ trendValue }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</template>
