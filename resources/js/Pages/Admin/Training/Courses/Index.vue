<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    courses: { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
});

function applyFilters() {
    filterForm.get(route('admin.training-courses.index'), { preserveScroll: true });
}

function destroy(course) {
    if (confirm(`Delete "${course.name}"? This cannot be undone.`)) {
        router.delete(route('admin.training-courses.destroy', course.id), { preserveScroll: true });
    }
}

const hasFilters = props.filters.search || props.filters.status;
</script>

<template>
    <Head title="Training Courses" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Training Courses
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Staff Training', href: route('admin.training-records.index') },
                    { label: 'Training Courses' },
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
                            title="Training Courses"
                            description="Catalog of staff training courses"
                        />
                        <Link
                            :href="route('admin.training-courses.create')"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
                        >+ Add Course</Link>
                    </div>

                    <!-- Filters -->
                    <div class="mb-4 flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Course name…"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div class="min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select
                                v-model="filterForm.status"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button
                            @click="applyFilters"
                            class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                        >Filter</button>
                        <Link
                            v-if="hasFilters"
                            :href="route('admin.training-courses.index')"
                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        >Clear</Link>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Course Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Trainer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="course in courses?.data ?? []" :key="course.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ course.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ course.trainer }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ course.description || '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            :class="course.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ course.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Link
                                            :href="route('admin.training-courses.edit', course.id)"
                                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline mr-3"
                                        >Edit</Link>
                                        <button
                                            @click="destroy(course)"
                                            class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                        >Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!courses?.data?.length">
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No training courses found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="courses?.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ courses.from }}–{{ courses.to }} of {{ courses.total }}
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="courses.prev_page_url"
                                :href="courses.prev_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Previous</Link>
                            <Link
                                v-if="courses.next_page_url"
                                :href="courses.next_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Next</Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
