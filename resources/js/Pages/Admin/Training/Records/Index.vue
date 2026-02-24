<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    records:   { type: Object, default: null },
    filters:   { type: Object, default: () => ({}) },
    courses:   { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
});

const filterForm = useForm({
    search:             props.filters.search             ?? '',
    employee_id:        props.filters.employee_id        ?? '',
    training_course_id: props.filters.training_course_id ?? '',
    date_from:          props.filters.date_from          ?? '',
    date_to:            props.filters.date_to            ?? '',
});

function applyFilters() {
    filterForm.get(route('admin.training-records.index'), { preserveScroll: true });
}

function destroy(record) {
    if (confirm('Delete this training record? This cannot be undone.')) {
        router.delete(route('admin.training-records.destroy', record.id), { preserveScroll: true });
    }
}

const hasFilters = props.filters.search || props.filters.employee_id || props.filters.training_course_id || props.filters.date_from || props.filters.date_to;
</script>

<template>
    <Head title="Training Records" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Staff Training Records
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Staff Training Records' },
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
                            title="Staff Training Records"
                            description="Log and review training completions for all staff"
                        />
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.training-courses.index')"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Manage Courses</Link>
                            <Link
                                :href="route('admin.training-records.create')"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
                            >+ Log Completion</Link>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Employee, course, trainer…"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Employee</label>
                            <select
                                v-model="filterForm.employee_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All employees</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.last_name }}, {{ emp.first_name }} ({{ emp.employee_id }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Course</label>
                            <select
                                v-model="filterForm.training_course_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All courses</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                            <input
                                v-model="filterForm.date_from"
                                type="date"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                            <input
                                v-model="filterForm.date_to"
                                type="date"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                @click="applyFilters"
                                class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                            >Filter</button>
                            <Link
                                v-if="hasFilters"
                                :href="route('admin.training-records.index')"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                            >Clear</Link>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Course</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Date Completed</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Trainer</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="record in records?.data ?? []" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                        {{ record.employee?.first_name }} {{ record.employee?.last_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ record.training_course?.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ record.date_completed?.substring(0, 10) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ record.trainer_name }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Link
                                            :href="route('admin.training-records.show', record.id)"
                                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline mr-3"
                                        >View</Link>
                                        <Link
                                            :href="route('admin.training-records.edit', record.id)"
                                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline mr-3"
                                        >Edit</Link>
                                        <button
                                            @click="destroy(record)"
                                            class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                        >Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!records?.data?.length">
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No training records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="records?.last_page > 1" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ records.from }}–{{ records.to }} of {{ records.total }}
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="records.prev_page_url"
                                :href="records.prev_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Previous</Link>
                            <Link
                                v-if="records.next_page_url"
                                :href="records.next_page_url"
                                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Next</Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
