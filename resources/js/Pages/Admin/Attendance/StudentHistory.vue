<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    records: Object,
});

function getStatusBadgeClass(status) {
    const classes = {
        present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        absent:  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        late:    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        excused: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
}
</script>

<template>
    <Head :title="`Attendance History — ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Student Attendance History
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Attendance', href: route('admin.attendance.index') },
                    { label: 'Student History' },
                ]" />

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            :title="`${student.first_name} ${student.last_name}`"
                            :description="`${student.student_id} — Attendance history (${records.total} records)`"
                        />
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="record in records.data" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ formatDate(record.date) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ record.class_model?.course?.name || 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ record.class_model?.section_name || '' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(record.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >
                                            {{ record.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ record.notes || '—' }}
                                    </td>
                                </tr>
                                <tr v-if="records.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No attendance records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-3">
                        <div
                            v-for="record in records.data"
                            :key="record.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatDate(record.date) }}
                                    </p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ record.class_model?.course?.name || 'N/A' }}
                                    </p>
                                    <p v-if="record.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ record.notes }}
                                    </p>
                                </div>
                                <span
                                    :class="getStatusBadgeClass(record.status)"
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize"
                                >
                                    {{ record.status }}
                                </span>
                            </div>
                        </div>
                        <div v-if="records.data.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No attendance records found.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="records.links && records.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ records.from }}</span>
                                to
                                <span class="font-medium">{{ records.to }}</span>
                                of
                                <span class="font-medium">{{ records.total }}</span>
                                records
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in records.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-primary-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 dark:ring-gray-600',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </Card>

                <div class="mt-6 px-4 sm:px-0">
                    <Link
                        :href="route('admin.students.show', student.id)"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Student
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
