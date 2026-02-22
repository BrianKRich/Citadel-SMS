<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    fields: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    readonly: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

function updateValue(fieldId, value) {
    emit('update:modelValue', { ...props.modelValue, [fieldId]: value });
}

function toggleField(field) {
    router.post(route('admin.custom-fields.toggle', field.id), {}, {
        preserveScroll: true,
        preserveState: true,
    });
}

function displayValue(field) {
    const val = field.pivot_value;
    if (val === null || val === undefined || val === '') return '—';
    if (field.field_type === 'boolean') return val === '1' || val === 'true' ? 'Yes' : 'No';
    return val;
}
</script>

<template>
    <div v-if="fields.length" class="space-y-4">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">
            Custom Fields
        </h3>

        <div v-for="field in fields" :key="field.id">
            <!-- Show page: skip inactive fields entirely -->
            <template v-if="readonly">
                <div v-if="field.is_active" class="flex gap-4">
                    <span class="w-40 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">{{ field.label }}</span>
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ displayValue(field) }}</span>
                </div>
            </template>

            <!-- Edit/Create: show all fields, but collapse inactive ones -->
            <template v-else>
                <div class="flex items-start gap-3">
                    <!-- Toggle button -->
                    <div class="flex flex-col items-center gap-1 pt-1">
                        <button
                            type="button"
                            @click="toggleField(field)"
                            :title="field.is_active ? 'Disable field' : 'Enable field'"
                            :class="[
                                'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                                field.is_active
                                    ? 'bg-primary-600'
                                    : 'bg-gray-200 dark:bg-gray-700'
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transform ring-0 transition duration-200',
                                    field.is_active ? 'translate-x-4' : 'translate-x-0'
                                ]"
                            />
                        </button>
                    </div>

                    <div class="flex-1 min-w-0">
                        <label :for="`cf_${field.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ field.label }}
                            <span v-if="!field.is_active" class="ml-1 text-xs text-gray-400 dark:text-gray-500">(disabled)</span>
                        </label>

                        <!-- Active field inputs -->
                        <template v-if="field.is_active">
                            <input
                                v-if="field.field_type === 'text'"
                                :id="`cf_${field.id}`"
                                :value="modelValue[field.id] ?? ''"
                                @input="updateValue(field.id, $event.target.value)"
                                type="text"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />

                            <textarea
                                v-else-if="field.field_type === 'textarea'"
                                :id="`cf_${field.id}`"
                                :value="modelValue[field.id] ?? ''"
                                @input="updateValue(field.id, $event.target.value)"
                                rows="3"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />

                            <input
                                v-else-if="field.field_type === 'number'"
                                :id="`cf_${field.id}`"
                                :value="modelValue[field.id] ?? ''"
                                @input="updateValue(field.id, $event.target.value)"
                                type="number"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />

                            <input
                                v-else-if="field.field_type === 'date'"
                                :id="`cf_${field.id}`"
                                :value="modelValue[field.id] ?? ''"
                                @input="updateValue(field.id, $event.target.value)"
                                type="date"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />

                            <div v-else-if="field.field_type === 'boolean'" class="flex items-center gap-2 mt-1">
                                <button
                                    type="button"
                                    :id="`cf_${field.id}`"
                                    @click="updateValue(field.id, modelValue[field.id] === '1' || modelValue[field.id] === true ? '0' : '1')"
                                    :class="[
                                        'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                                        (modelValue[field.id] === '1' || modelValue[field.id] === true)
                                            ? 'bg-primary-600'
                                            : 'bg-gray-200 dark:bg-gray-700'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition duration-200',
                                            (modelValue[field.id] === '1' || modelValue[field.id] === true) ? 'translate-x-5' : 'translate-x-0'
                                        ]"
                                    />
                                </button>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ (modelValue[field.id] === '1' || modelValue[field.id] === true) ? 'Yes' : 'No' }}
                                </span>
                            </div>

                            <select
                                v-else-if="field.field_type === 'select'"
                                :id="`cf_${field.id}`"
                                :value="modelValue[field.id] ?? ''"
                                @change="updateValue(field.id, $event.target.value)"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Select —</option>
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
