<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    assessment: Object,
    gradeStats: Object,
});

function getStatusBadgeClass(status) {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return classes[status] || classes.draft;
}

function deleteAssessment() {
    if (confirm('Are you sure you want to delete this assessment?')) {
        router.delete(route('admin.assessments.destroy', props.assessment.id));
    }
}
</script>

<template>
    <Head :title="`Assessment: ${assessment.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Assessment Details
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>

                <!-- Assessment Info Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                        <div>
                            <PageHeader
                                :title="assessment.name"
                                :description="`${assessment.class_model?.course?.name || 'N/A'} — ${assessment.class_model?.section_name || ''}`"
                            />
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    :class="getStatusBadgeClass(assessment.status)"
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ assessment.status }}
                                </span>
                                <span v-if="assessment.is_extra_credit" class="inline-flex rounded-full bg-yellow-100 dark:bg-yellow-900 px-2 py-1 text-xs font-semibold text-yellow-800 dark:text-yellow-300">
                                    Extra Credit
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <Link :href="route('admin.grades.enter', assessment.id)">
                                <PrimaryButton>
                                    Enter Grades
                                </PrimaryButton>
                            </Link>
                            <Link
                                :href="route('admin.assessments.edit', assessment.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Edit
                            </Link>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ assessment.assessment_category?.name || '—' }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Max Score</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ assessment.max_score }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ assessment.due_date ? new Date(assessment.due_date).toLocaleDateString() : '—' }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Weight</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ assessment.weight ? `${(assessment.weight * 100).toFixed(0)}%` : 'Uses category weight' }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Grade Statistics Card -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Grade Statistics</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Summary of submitted grades for this assessment</p>
                    </div>

                    <div v-if="gradeStats && gradeStats.count > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ gradeStats.count }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Submitted</p>
                        </div>
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/30 p-4 text-center">
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ gradeStats.avg !== null ? Number(gradeStats.avg).toFixed(1) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Average</p>
                        </div>
                        <div class="rounded-lg bg-green-50 dark:bg-green-900/30 p-4 text-center">
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                                {{ gradeStats.max !== null ? Number(gradeStats.max).toFixed(1) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Highest</p>
                        </div>
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/30 p-4 text-center">
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300">
                                {{ gradeStats.min !== null ? Number(gradeStats.min).toFixed(1) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lowest</p>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No grades have been entered yet.
                        <Link
                            :href="route('admin.grades.enter', assessment.id)"
                            class="ml-1 text-primary-600 dark:text-primary-400 hover:underline"
                        >
                            Enter grades now
                        </Link>
                    </div>
                </Card>

                <!-- Grades List Card -->
                <Card v-if="assessment.grades && assessment.grades.length > 0">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Individual Grades</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Percentage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Late</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="grade in assessment.grades" :key="grade.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ grade.enrollment?.student?.first_name }} {{ grade.enrollment?.student?.last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ grade.enrollment?.student?.student_id }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ grade.score }} / {{ assessment.max_score }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ assessment.max_score > 0 ? ((grade.score / assessment.max_score) * 100).toFixed(1) : '—' }}%
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span v-if="grade.is_late" class="inline-flex rounded-full bg-yellow-100 dark:bg-yellow-900 px-2 text-xs font-semibold leading-5 text-yellow-800 dark:text-yellow-300">
                                            Late
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                            {{ grade.notes || '—' }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Back Link -->
                <div class="px-4 sm:px-0">
                    <Link
                        :href="route('admin.assessments.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Assessments
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
