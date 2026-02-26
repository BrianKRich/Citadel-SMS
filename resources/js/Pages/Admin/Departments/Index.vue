<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    departments: { type: Object, default: null },
    filters:     { type: Object, default: () => ({}) },
});

const filterForm = useForm({
    search: props.filters.search ?? '',
});

function applyFilters() {
    filterForm.get(route('admin.departments.index'), { preserveScroll: true });
}

function destroy(department) {
    if (confirm(`Delete "${department.name}"? This cannot be undone.`)) {
        router.delete(route('admin.departments.destroy', department.id), { preserveScroll: true });
    }
}

const hasFilters = props.filters.search;
</script>

<template>
    <Head title="Departments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Departments
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academy Setup', href: route('admin.academy.index') },
                    { label: 'Departments' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
                        <PageHeader
                            title="Departments"
                            description="Manage the departments within your academy."
                        />
                        <Link
                            :href="route('admin.departments.create')"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
                        >+ Add Department</Link>
                    </div>

                    <!-- Search -->
                    <div class="mb-4 flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Department name…"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <button
                            @click="applyFilters"
                            class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                        >Filter</button>
                        <Link
                            v-if="hasFilters"
                            :href="route('admin.departments.index')"
                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        >Clear</Link>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Roles</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Employees</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="dept in departments?.data ?? []" :key="dept.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ dept.name }}
                                        <span
                                            v-if="dept.is_system"
                                            class="ml-2 inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >System</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ dept.roles_count }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ dept.employees_count }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <template v-if="!dept.is_system">
                                            <Link
                                                :href="route('admin.departments.edit', dept.id)"
                                                class="text-sm text-primary-600 dark:text-primary-400 hover:underline mr-3"
                                            >Edit</Link>
                                            <button
                                                @click="destroy(dept)"
                                                class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                            >Delete</button>
                                        </template>
                                        <span v-else class="text-xs text-gray-400 dark:text-gray-600 italic">Protected</span>
                                    </td>
                                </tr>
                                <tr v-if="!departments?.data?.length">
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No departments found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="departments?.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ departments.from }}–{{ departments.to }} of {{ departments.total }}
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="departments.prev_page_url"
                                :href="departments.prev_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Previous</Link>
                            <Link
                                v-if="departments.next_page_url"
                                :href="departments.next_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Next</Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
