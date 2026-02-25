<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    cohortCourse: { type: Object, required: true },
});

const cc = computed(() => props.cohortCourse);
const courseName = computed(() => cc.value.course?.name ?? 'Course Assignment');

function fmtDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.substring(0, 10).split('-');
    return `${m}-${d}-${y}`;
}

const getStatusBadge = (s) => {
    const map = {
        open:        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed:      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed:   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.open;
};

const getEnrollmentStatusBadge = (s) => {
    const map = {
        active:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        dropped:   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return map[s] || map.active;
};

const instructorLabel = computed(() => {
    if (cc.value.instructor_type === 'staff' && cc.value.employee) {
        return `${cc.value.employee.first_name} ${cc.value.employee.last_name}`;
    }
    return cc.value.institution?.name ?? '—';
});

const cohortLabel = computed(() => {
    const name = cc.value.cohort?.name;
    if (name === 'alpha') return 'Cohort Alpha';
    if (name === 'bravo') return 'Cohort Bravo';
    return name ?? '—';
});

const scheduleDisplay = computed(() => {
    const schedule = cc.value.schedule;
    if (!schedule || !Array.isArray(schedule) || schedule.length === 0) return '—';
    return schedule.map(s => `${s.day} ${s.start_time}–${s.end_time}`).join(', ');
});

function destroy() {
    if (confirm('Delete this course assignment? This cannot be undone.')) {
        router.delete(route('admin.cohort-courses.destroy', cc.value.id));
    }
}
</script>

<template>
    <Head :title="courseName" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ courseName }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Course Assignments', href: route('admin.cohort-courses.index') },
                    { label: courseName },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Info Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex-1">
                            <PageHeader :title="courseName" description="Course assignment details." />
                            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Class</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">
                                        <Link
                                            v-if="cc.cohort?.class_id"
                                            :href="route('admin.classes.show', cc.cohort.class_id)"
                                            class="text-primary-600 dark:text-primary-400 hover:underline"
                                        >Class {{ cc.cohort?.class?.class_number ?? '—' }}</Link>
                                        <span v-else>—</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Cohort</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ cohortLabel }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Instructor Type</dt>
                                    <dd class="text-gray-900 dark:text-gray-100 capitalize">{{ cc.instructor_type?.replace('_', ' ') ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Instructor</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ instructorLabel }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Room</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ cc.room ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Schedule</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ scheduleDisplay }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span :class="getStatusBadge(cc.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                            {{ cc.status?.replace('_', ' ') }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Enrollment</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">
                                        {{ cc.enrolled_count ?? 0 }} / {{ cc.max_students ?? '—' }} students
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.cohort-courses.edit', cc.id)"
                                class="inline-flex items-center rounded-md bg-secondary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-700"
                            >Edit</Link>
                            <button
                                type="button"
                                @click="destroy"
                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700"
                            >Delete</button>
                        </div>
                    </div>
                </Card>

                <!-- Enrolled Students Card -->
                <Card>
                    <div class="flex items-center justify-between mb-2">
                        <PageHeader title="Enrolled Students" :description="`${(cc.enrollments ?? []).length} student${(cc.enrollments ?? []).length === 1 ? '' : 's'}`" />
                        <Link
                            :href="route('admin.enrollment.create') + '?cohort_course_id=' + cc.id"
                            class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                        >+ Enroll Student</Link>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Student ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Enrollment Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-if="!cc.enrollments || cc.enrollments.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No students enrolled yet.</td>
                                </tr>
                                <tr
                                    v-for="enrollment in cc.enrollments"
                                    :key="enrollment.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ enrollment.student?.student_id ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <Link
                                            v-if="enrollment.student_id"
                                            :href="route('admin.students.show', enrollment.student_id)"
                                            class="text-primary-600 dark:text-primary-400 hover:underline"
                                        >
                                            {{ enrollment.student ? `${enrollment.student.first_name} ${enrollment.student.last_name}` : '—' }}
                                        </Link>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ fmtDate(enrollment.enrollment_date) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="getEnrollmentStatusBadge(enrollment.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize">
                                            {{ enrollment.status ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
