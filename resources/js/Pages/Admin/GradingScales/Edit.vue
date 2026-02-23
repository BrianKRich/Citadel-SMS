<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    scale: Object,
});

const form = useForm({
    name: props.scale.name,
    is_default: props.scale.is_default,
    entries: props.scale.entries.map(e => ({
        letter: e.letter,
        min_percentage: e.min_percentage,
        gpa_points: e.gpa_points,
    })),
});

function addEntry() {
    form.entries.push({ letter: '', min_percentage: '', gpa_points: '' });
}

function removeEntry(index) {
    form.entries.splice(index, 1);
}

function submit() {
    form.put(route('admin.grading-scales.update', props.scale.id));
}
</script>

<template>
    <Head :title="`Edit Scale: ${scale.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Grading Scale
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Grade Management', href: route('admin.grades.index') },
                    { label: 'Edit Grading Scale' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            :title="`Edit: ${scale.name}`"
                            description="Update this grading scale's entries"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Scale Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Is Default -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_default"
                                v-model="form.is_default"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                            />
                            <label for="is_default" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Set as default grading scale
                            </label>
                        </div>

                        <!-- Scale Entries -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Scale Entries <span class="text-red-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="addEntry"
                                    class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 font-medium"
                                >
                                    + Add Entry
                                </button>
                            </div>

                            <div class="overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Letter Grade
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Min Percentage (%)
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                GPA Points
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Remove
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                        <tr v-for="(entry, index) in form.entries" :key="index">
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model="entry.letter"
                                                    type="text"
                                                    maxlength="3"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                                />
                                                <p v-if="form.errors[`entries.${index}.letter`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                    {{ form.errors[`entries.${index}.letter`] }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model="entry.min_percentage"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                                />
                                                <p v-if="form.errors[`entries.${index}.min_percentage`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                    {{ form.errors[`entries.${index}.min_percentage`] }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    v-model="entry.gpa_points"
                                                    type="number"
                                                    step="0.1"
                                                    min="0"
                                                    max="4"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                                />
                                                <p v-if="form.errors[`entries.${index}.gpa_points`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                    {{ form.errors[`entries.${index}.gpa_points`] }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    @click="removeEntry(index)"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 text-sm"
                                                    :disabled="form.entries.length <= 1"
                                                >
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-if="form.errors.entries" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.entries }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Entries will be sorted by minimum percentage automatically.
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Update Grading Scale' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.grading-scales.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.grading-scales.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            ← Back to Grading Scales
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
