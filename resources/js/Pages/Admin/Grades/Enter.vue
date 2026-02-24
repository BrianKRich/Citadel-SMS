<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    assessment: Object,
    enrollments: Array,
});

// Build form with one entry per enrollment
const form = useForm({
    grades: props.enrollments.map(enrollment => {
        const existing = enrollment.grades?.[0];
        return {
            enrollment_id: enrollment.id,
            assessment_id: props.assessment.id,
            score: existing?.score ?? '',
            is_late: existing?.is_late ?? false,
            late_penalty: existing?.late_penalty ?? '',
            notes: existing?.notes ?? '',
        };
    }),
});

function submit() {
    form.post(route('admin.grades.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Enter Grades — ${assessment.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Enter Grades
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Grade Management', href: route('admin.grades.index') },
                    { label: 'Enter Grades' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Assessment Info -->
                <Card class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <PageHeader
                            :title="assessment.name"
                            :description="`${assessment.cohort_course?.course?.name || 'N/A'}`"
                        />
                        <div class="flex-shrink-0 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                            <div>Max Score: <span class="font-medium text-gray-900 dark:text-gray-100">{{ assessment.max_score }}</span></div>
                            <div v-if="assessment.due_date">
                                Due: <span class="font-medium text-gray-900 dark:text-gray-100">{{ new Date(assessment.due_date).toLocaleDateString() }}</span>
                            </div>
                            <div>Category: <span class="font-medium text-gray-900 dark:text-gray-100">{{ assessment.assessment_category?.name || '—' }}</span></div>
                        </div>
                    </div>
                </Card>

                <!-- Grade Entry Form -->
                <Card>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Student Grades
                            <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ enrollments.length }} students)
                            </span>
                        </h3>
                    </div>

                    <form @submit.prevent="submit">
                        <div v-if="enrollments.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No students are enrolled in this class.
                        </div>

                        <!-- Desktop Table -->
                        <div v-if="enrollments.length > 0" class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Score (max: {{ assessment.max_score }})
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Late</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Late Penalty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-for="(gradeEntry, index) in form.grades" :key="gradeEntry.enrollment_id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ enrollments[index]?.student?.first_name }} {{ enrollments[index]?.student?.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ enrollments[index]?.student?.student_id }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="gradeEntry.score"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :max="assessment.max_score"
                                                placeholder="—"
                                                class="block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                            <p v-if="form.errors[`grades.${index}.score`]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                {{ form.errors[`grades.${index}.score`] }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input
                                                v-model="gradeEntry.is_late"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                            />
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="gradeEntry.late_penalty"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="100"
                                                placeholder="0"
                                                :disabled="!gradeEntry.is_late"
                                                class="block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-40"
                                            />
                                        </td>
                                        <td class="px-6 py-4">
                                            <textarea
                                                v-model="gradeEntry.notes"
                                                rows="1"
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
                                v-for="(gradeEntry, index) in form.grades"
                                :key="gradeEntry.enrollment_id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ enrollments[index]?.student?.first_name }} {{ enrollments[index]?.student?.last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ enrollments[index]?.student?.student_id }}
                                        </p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Score (max: {{ assessment.max_score }})
                                        </label>
                                        <input
                                            v-model="gradeEntry.score"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :max="assessment.max_score"
                                            placeholder="—"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input
                                            v-model="gradeEntry.is_late"
                                            :id="`late-${index}`"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                        />
                                        <label :for="`late-${index}`" class="text-sm font-medium text-gray-700 dark:text-gray-300">Late Submission</label>
                                    </div>
                                    <div v-if="gradeEntry.is_late">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Late Penalty (%)</label>
                                        <input
                                            v-model="gradeEntry.late_penalty"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                        <textarea
                                            v-model="gradeEntry.notes"
                                            rows="2"
                                            placeholder="Optional notes..."
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="enrollments.length > 0" class="mt-6 flex items-center gap-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save All Grades' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.assessments.show', assessment.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </Card>

                <div class="mt-6">
                    <Link
                        :href="route('admin.assessments.show', assessment.id)"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Assessment
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
