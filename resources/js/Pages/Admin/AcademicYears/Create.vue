<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    start_date: '',
    end_date: '',
    is_current: false,
    terms: [],
});

function addTerm() {
    form.terms.push({ name: '', start_date: '', end_date: '', is_current: false });
}

function removeTerm(idx) {
    form.terms.splice(idx, 1);
}

function submit() {
    form.post(route('admin.academic-years.store'));
}
</script>

<template>
    <Head title="Create Academic Year" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Create Academic Year
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academic Years', href: route('admin.academic-years.index') },
                    { label: 'Add Academic Year' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            title="Add Academic Year"
                            description="Create a new academic year and optionally define its terms"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. 2024-2025"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.start_date }}
                            </p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                End Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.end_date }}
                            </p>
                        </div>

                        <!-- Is Current -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="form.is_current"
                                    type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Set as current academic year
                                </span>
                            </label>
                            <p v-if="form.errors.is_current" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.is_current }}
                            </p>
                        </div>

                        <!-- Terms Section -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Terms (optional)
                                </label>
                                <button
                                    type="button"
                                    @click="addTerm"
                                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                >
                                    + Add Term
                                </button>
                            </div>

                            <div v-if="form.terms.length" class="space-y-4">
                                <div
                                    v-for="(term, idx) in form.terms"
                                    :key="idx"
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                                >
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            Term {{ idx + 1 }}
                                        </h4>
                                        <button
                                            type="button"
                                            @click="removeTerm(idx)"
                                            class="text-xs text-red-600 hover:text-red-700 dark:text-red-400"
                                        >
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Term Name <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="term.name"
                                                type="text"
                                                placeholder="e.g. Fall 2024"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                            <p v-if="form.errors[`terms.${idx}.name`]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ form.errors[`terms.${idx}.name`] }}
                                            </p>
                                        </div>

                                        <div class="flex items-end pb-1">
                                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <input
                                                    v-model="term.is_current"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                                />
                                                Current Term
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Start Date <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="term.start_date"
                                                type="date"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                            <p v-if="form.errors[`terms.${idx}.start_date`]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ form.errors[`terms.${idx}.start_date`] }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                End Date <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="term.end_date"
                                                type="date"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                            <p v-if="form.errors[`terms.${idx}.end_date`]" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {{ form.errors[`terms.${idx}.end_date`] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                                No terms added yet. You can add terms after creating the academic year.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Academic Year' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.academic-years.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.academic-years.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            &larr; Back to Academic Years
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
