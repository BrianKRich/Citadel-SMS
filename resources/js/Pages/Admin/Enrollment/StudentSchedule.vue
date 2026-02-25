<template>
    <Head :title="`Schedule — ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Breadcrumb :items="[
                { label: 'Dashboard', href: route('admin.dashboard') },
                { label: 'Enrollment', href: route('admin.enrollment.index') },
                { label: 'Student Schedule' },
            ]" />

            <div v-if="$page.props.flash?.success" class="mb-4">
                <Alert type="success" :message="$page.props.flash.success" />
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4">
                <Alert type="error" :message="$page.props.flash.error" />
            </div>

            <!-- Student Info Card -->
            <Card class="mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                <span class="text-lg font-semibold text-primary-700 dark:text-primary-300">
                                    {{ student.first_name?.[0] }}{{ student.last_name?.[0] }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ student.first_name }} {{ student.last_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ student.student_id }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <Link
                                :href="route('admin.enrollment.index', { student_id: student.id })"
                                class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                            >
                                View All Enrollments for Student
                            </Link>
                            <Link
                                :href="route('admin.enrollment.index')"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                &larr; Back to Enrollments
                            </Link>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Schedule Card -->
            <Card>
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Schedule</h2>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Course Code
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Course Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Class / Cohort
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Instructor
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Room
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Schedule
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="enrollments.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No current enrollments found.
                                    </td>
                                </tr>
                                <tr
                                    v-for="enrollment in enrollments"
                                    :key="enrollment.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                >
                                    <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900 dark:text-gray-100">
                                        {{ enrollment.classCourse?.course?.course_code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.classCourse?.course?.name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        Class {{ enrollment.classCourse?.class?.class_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ enrollment.classCourse?.employee?.first_name }} {{ enrollment.classCourse?.employee?.last_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ enrollment.classCourse?.room || '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <template v-if="enrollment.classCourse?.schedule?.length">
                                            <div v-for="(slot, i) in enrollment.classCourse.schedule" :key="i" class="whitespace-nowrap">
                                                {{ formatScheduleSlot(slot) }}
                                            </div>
                                        </template>
                                        <span v-else class="text-gray-400 dark:text-gray-500">No schedule</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="block sm:hidden space-y-4">
                        <div v-if="enrollments.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No current enrollments found.
                        </div>
                        <div
                            v-for="enrollment in enrollments"
                            :key="enrollment.id"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 space-y-2"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-mono font-medium text-gray-900 dark:text-gray-100">
                                    {{ enrollment.classCourse?.course?.course_code }}
                                </p>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Class {{ enrollment.classCourse?.class?.class_number }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ enrollment.classCourse?.course?.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Class {{ enrollment.classCourse?.class?.class_number ?? '—' }}
                                <template v-if="enrollment.classCourse?.room">
                                    &bull; Room: {{ enrollment.classCourse.room }}
                                </template>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Instructor: {{ enrollment.classCourse?.employee?.first_name }} {{ enrollment.classCourse?.employee?.last_name }}
                            </p>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <template v-if="enrollment.classCourse?.schedule?.length">
                                    <div v-for="(slot, i) in enrollment.classCourse.schedule" :key="i">
                                        {{ formatScheduleSlot(slot) }}
                                    </div>
                                </template>
                                <span v-else>No schedule</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                        Showing {{ enrollments.length }} enrolled course(s) for {{ student.first_name }} {{ student.last_name }}.
                    </p>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    enrollments: Array,
});

const DAY_ABBR = {
    monday: 'Mon',
    tuesday: 'Tue',
    wednesday: 'Wed',
    thursday: 'Thu',
    friday: 'Fri',
    saturday: 'Sat',
    sunday: 'Sun',
};

function formatTime(time) {
    if (!time) return '';
    const [h, m] = time.split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${period}`;
}

function formatScheduleSlot(slot) {
    const day = DAY_ABBR[slot.day?.toLowerCase()] ?? slot.day ?? '';
    const start = formatTime(slot.start_time);
    const end = formatTime(slot.end_time);
    if (start && end) {
        return `${day} ${start}–${end}`;
    }
    return day;
}
</script>
