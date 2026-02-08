<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
    get() {
        return props.modelValue;
    },
    set(newValue) {
        emit('update:modelValue', newValue);
    },
});

const inputId = computed(() => props.id || `color-picker-${props.label.toLowerCase().replace(/\s+/g, '-')}`);
</script>

<template>
    <div>
        <InputLabel :for="inputId" :value="label" />

        <div class="mt-1 flex items-center space-x-3">
            <!-- Color Picker -->
            <input
                :id="inputId"
                type="color"
                v-model="value"
                class="h-10 w-20 cursor-pointer rounded border-gray-300 transition hover:border-gray-400"
            />

            <!-- Hex Input -->
            <input
                type="text"
                v-model="value"
                placeholder="#000000"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                pattern="^#[0-9A-Fa-f]{6}$"
            />
        </div>

        <InputError class="mt-2" :message="error" />
    </div>
</template>
