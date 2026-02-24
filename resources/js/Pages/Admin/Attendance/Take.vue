<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    cohortCourse: Object,
    enrollments: Array,
    existingRecords: Object,
    date: String,
});

const selectedDate = ref(props.date);

watch(selectedDate, (newDate) => {
    router.get(
        route('admin.attendance.take', props.cohortCourse.id),
        { date: newDate },
        { preserveScroll: false }
    );
});

const statusOptions = [
    { value: 'present', label: 'Present' },
    { value: 'absent',  label: 'Absent' },
    { value: 'late',    label: 'Late' },
    { value: 'excused', label: 'Excused' },
];

const form = useForm({
    cohort_course_id: props.cohortCourse.id,
    date: props.date,
    records: props.enrollments.map(enrollment => {
        const existing = props.existingRecords?.[enrollment.student_id];
        return {
            student_id: enrollment.student_id,
            status: existing?.status ?? 'present',
            notes: existing?.notes ?? '',
        };
    }),
});

function submit() {
    form.post(route('admin.attendance.store'), {
        preserveScroll: true,
    });
}

function getStatusButtonClass(status, selected) {
    if (status === selected) {
        const active = {
            present: 'bg-green-600 text-white border-green-600',
            absent:  'bg-red-600 text-white border-red-600',
            late:    'bg-yellow-500 text-white border-yellow-500',
            excused: 'bg-blue-600 text-white border-blue-600',
        };
        return active[status] || 'bg-gray-600 text-white';
    }
    return 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700';
}
</script>

<template>
    <Head :title="`Take Attendance — ${cohortCourse.course?.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Take Attendance
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Attendance', href: route('admin.attendance.index') },
                    { label: 'Take Attendance' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Cohort Course Info -->
                <Card class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <PageHeader
                            :title="cohortCourse.course?.name || 'N/A'"
                            :description="`Class ${cohortCourse.cohort?.class?.class_number} / ${cohortCourse.cohort?.name}`"
                        />
                        <div class="flex-shrink-0">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                            <input
                                v-model="selectedDate"
                                type="date"
                                class="block rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                        </div>
                    </div>
                </Card>

                <!-- Attendance Form -->
                <Card>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Student Attendance
                            <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ enrollments.length }} students)
                            </span>
                        </h3>
                    </div>

                    <form @submit.prevent="submit">
                        <div v-if="enrollments.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No students are enrolled in this cohort course.
                        </div>

                        <!-- Desktop Table -->
                        <div v-if="enrollments.length > 0" class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-for="(record, index) in form.records" :key="record.student_id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ enrollments[index]?.student?.first_name }} {{ enrollments[index]?.student?.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ enrollments[index]?.student?.student_id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-1">
                                                <button
                                                    v-for="opt in statusOptions"
                                                    :key="opt.value"
                                                    type="button"
                                                    @click="record.status = opt.value"
                                                    :class="getStatusButtonClass(opt.value, record.status)"
                                                    class="rounded border px-2 py-1 text-xs font-medium transition-colors"
                                                >
                                                    {{ opt.label }}
                                                </button>
                                            </div>
                                            <p v-if="form.errors[`records.${index}.status`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                {{ form.errors[`records.${index}.status`] }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="record.notes"
                                                type="text"
                                                placeholder="Optional notes..."
                                                class="block w-full min-w-[160px] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile: Card per student -->
                        <div v-if="enrollments.length > 0" class="md:hidden space-y-4">
                            <div
                                v-for="(record, index) in form.records"
                                :key="record.student_id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800"
                            >
                                <div class="mb-3">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ enrollments[index]?.student?.first_name }} {{ enrollments[index]?.student?.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ enrollments[index]?.student?.student_id }}
                                    </p>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                        <div class="flex gap-1 flex-wrap">
                                            <button
                                                v-for="opt in statusOptions"
                                                :key="opt.value"
                                                type="button"
                                                @click="record.status = opt.value"
                                                :class="getStatusButtonClass(opt.value, record.status)"
                                                class="rounded border px-3 py-1.5 text-sm font-medium transition-colors min-h-[44px]"
                                            >
                                                {{ opt.label }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                        <input
                                            v-model="record.notes"
                                            type="text"
                                            placeholder="Optional notes..."
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="enrollments.length > 0" class="mt-6 flex items-center gap-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Attendance' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.attendance.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </Card>

                <div class="mt-6">
                    <Link
                        :href="route('admin.attendance.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Attendance
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
