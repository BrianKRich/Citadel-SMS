<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    academicYear: Object,
});

const form = useForm({
    name:       props.academicYear.name ?? '',
    start_date: props.academicYear.start_date ? props.academicYear.start_date.substring(0, 10) : '',
    end_date:   props.academicYear.end_date ? props.academicYear.end_date.substring(0, 10) : '',
    status:     props.academicYear.status ?? 'forming',
});

function submit() {
    form.patch(route('admin.academic-years.update', props.academicYear.id));
}
</script>

<template>
    <Head :title="`Edit: ${academicYear.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Academic Year
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academic Years', href: route('admin.academic-years.index') },
                    { label: academicYear.name, href: route('admin.academic-years.show', academicYear.id) },
                    { label: 'Edit' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            :title="`Edit: ${academicYear.name}`"
                            description="Update this academic year's details. Terms are managed from the detail page."
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

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="forming">Forming</option>
                                <option value="current">Current</option>
                                <option value="completed">Complete</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Update Academic Year' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.academic-years.show', academicYear.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.academic-years.show', academicYear.id)"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            &larr; Back to {{ academicYear.name }}
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
