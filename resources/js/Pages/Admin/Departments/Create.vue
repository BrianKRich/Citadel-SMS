<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

function submit() {
    form.post(route('admin.departments.store'));
}
</script>

<template>
    <Head title="New Department" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                New Department
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academy Setup', href: route('admin.academy.index') },
                    { label: 'Departments', href: route('admin.departments.index') },
                    { label: 'New Department' },
                ]" />

                <Card>
                    <PageHeader title="New Department" />

                    <form @submit.prevent="submit" class="mt-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Department Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                autofocus
                                placeholder="e.g. Academic Affairs"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('admin.departments.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving…' : 'Save Department' }}
                            </button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
