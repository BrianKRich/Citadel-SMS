<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    entity_type: '',
    label: '',
    field_type: '',
    options: [],
    sort_order: 0,
    is_active: true,
});

const namePreview = computed(() => {
    return form.label
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
});

function addOption() {
    form.options.push('');
}

function removeOption(index) {
    form.options.splice(index, 1);
}

function submit() {
    form.post(route('admin.custom-fields.store'));
}
</script>

<template>
    <Head title="Add Custom Field" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Custom Field</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            title="Add Custom Field"
                            description="Define a new custom field to appear on entity forms and detail pages"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Entity Type -->
                        <div>
                            <label for="entity_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Entity <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="entity_type"
                                v-model="form.entity_type"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Select entity —</option>
                                <option value="Student">Student</option>
                                <option value="Employee">Employee</option>
                                <option value="Course">Course</option>
                                <option value="Class">Class</option>
                                <option value="Enrollment">Enrollment</option>
                            </select>
                            <p v-if="form.errors.entity_type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.entity_type }}</p>
                        </div>

                        <!-- Label -->
                        <div>
                            <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Label <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="label"
                                v-model="form.label"
                                type="text"
                                placeholder="e.g. Has IEP"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.label" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Field name: <code class="font-mono">{{ namePreview }}</code>
                            </p>
                            <p v-if="form.errors.label" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.label }}</p>
                        </div>

                        <!-- Field Type -->
                        <div>
                            <label for="field_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Field Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="field_type"
                                v-model="form.field_type"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Select type —</option>
                                <option value="text">Text</option>
                                <option value="textarea">Text Area</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="boolean">Boolean (Yes/No)</option>
                                <option value="select">Select (Dropdown)</option>
                            </select>
                            <p v-if="form.errors.field_type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.field_type }}</p>
                        </div>

                        <!-- Options (for select type) -->
                        <div v-if="form.field_type === 'select'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Options <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <div
                                    v-for="(option, index) in form.options"
                                    :key="index"
                                    class="flex gap-2"
                                >
                                    <input
                                        v-model="form.options[index]"
                                        type="text"
                                        :placeholder="`Option ${index + 1}`"
                                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <button
                                        type="button"
                                        @click="removeOption(index)"
                                        class="text-sm text-red-600 hover:text-red-500 px-2"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    @click="addOption"
                                    class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
                                >
                                    + Add option
                                </button>
                            </div>
                            <p v-if="form.errors.options" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.options }}</p>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sort Order
                            </label>
                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first (default: 0)</p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                                    form.is_active ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition duration-200',
                                        form.is_active ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                />
                            </button>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ form.is_active ? 'Active — will appear on forms' : 'Inactive — hidden from forms' }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('admin.custom-fields.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                ← Back to Custom Fields
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                Create Field
                            </PrimaryButton>
                        </div>

                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
