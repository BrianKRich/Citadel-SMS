<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    classModel: Object,
    summaries: Array,
    filters: Object,
});

const dateFrom = ref(props.filters?.date_from || '');
const dateTo   = ref(props.filters?.date_to || '');

function applyFilter() {
    router.get(
        route('admin.attendance.summary', props.classModel.id),
        {
            date_from: dateFrom.value || undefined,
            date_to:   dateTo.value || undefined,
        },
        { preserveState: true, replace: true }
    );
}

function clearFilter() {
    dateFrom.value = '';
    dateTo.value   = '';
    router.get(route('admin.attendance.summary', props.classModel.id));
}

function getRateClass(rate) {
    if (rate === null || rate === undefined) return 'text-gray-400';
    if (rate >= 90) return 'text-green-600 dark:text-green-400';
    if (rate >= 75) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-red-600 dark:text-red-400';
}
</script>

<template>
    <Head :title="`Attendance Summary — ${classModel.course?.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Attendance Summary
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Class Info + Date Filter -->
                <Card class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <PageHeader
                            :title="classModel.course?.name || 'N/A'"
                            :description="`${classModel.section_name} — ${classModel.term?.name || ''}`"
                        />
                        <div class="flex-shrink-0 space-y-2">
                            <div class="flex items-center gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                    <input
                                        v-model="dateFrom"
                                        type="date"
                                        class="block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                    <input
                                        v-model="dateTo"
                                        type="date"
                                        class="block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="applyFilter"
                                    class="rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700"
                                >
                                    Filter
                                </button>
                                <button
                                    v-if="filters?.date_from || filters?.date_to"
                                    @click="clearFilter"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Summary Table -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Per-Student Summary
                            <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ summaries.length }} students)
                            </span>
                        </h3>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Student</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">Present</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-red-600 dark:text-red-400">Absent</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-yellow-600 dark:text-yellow-400">Late</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">Excused</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Attendance %</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">History</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="summary in summaries" :key="summary.student?.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ summary.student?.first_name }} {{ summary.student?.last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ summary.student?.student_id }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-900 dark:text-gray-100">
                                        {{ summary.total }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-green-600 dark:text-green-400">
                                        {{ summary.present }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-red-600 dark:text-red-400">
                                        {{ summary.absent }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-yellow-600 dark:text-yellow-400">
                                        {{ summary.late }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ summary.excused }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-semibold" :class="getRateClass(summary.attendance_rate)">
                                        {{ summary.attendance_rate !== null ? summary.attendance_rate + '%' : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.attendance.student', summary.student?.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >
                                            View History
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="summaries.length === 0">
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No students enrolled in this class.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="summary in summaries"
                            :key="summary.student?.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ summary.student?.first_name }} {{ summary.student?.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ summary.student?.student_id }}</p>
                                </div>
                                <span class="text-sm font-bold" :class="getRateClass(summary.attendance_rate)">
                                    {{ summary.attendance_rate !== null ? summary.attendance_rate + '%' : '—' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-4 gap-2 text-center text-xs mb-3">
                                <div>
                                    <div class="font-semibold text-green-600 dark:text-green-400">{{ summary.present }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">Present</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-red-600 dark:text-red-400">{{ summary.absent }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">Absent</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-yellow-600 dark:text-yellow-400">{{ summary.late }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">Late</div>
                                </div>
                                <div>
                                    <div class="font-semibold text-blue-600 dark:text-blue-400">{{ summary.excused }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">Excused</div>
                                </div>
                            </div>
                            <Link
                                :href="route('admin.attendance.student', summary.student?.id)"
                                class="block text-center rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                View History
                            </Link>
                        </div>
                        <div v-if="summaries.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No students enrolled in this class.
                        </div>
                    </div>
                </Card>

                <div class="mt-6 px-4 sm:px-0 flex gap-4">
                    <Link
                        :href="route('admin.attendance.take', classModel.id)"
                        class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                    >
                        Take Attendance
                    </Link>
                    <Link
                        :href="route('admin.attendance.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Attendance
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
