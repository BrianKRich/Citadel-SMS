<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    student: Object,
    cumulativeGpa: Number,
});

// student has: enrollments (with cohort_course.course, cohort_course.cohort.class, grades, weighted_average, final_letter_grade, gpa_points)
const enrollments = computed(() => props.student.enrollments || []);

function getLetterGradeBadgeClass(letter) {
    if (!letter) return 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400';
    const map = {
        A: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        B: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        C: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        D: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        F: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return map[letter?.[0]?.toUpperCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        dropped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || classes.active;
}
</script>

<template>
    <Head :title="`Grades — ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Student Grades
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Grade Management', href: route('admin.grades.index') },
                    { label: 'Student Grades' },
                ]" />

                <!-- Student Summary Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Avatar placeholder -->
                            <div class="h-14 w-14 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold text-primary-700 dark:text-primary-300">
                                    {{ student.first_name?.[0] }}{{ student.last_name?.[0] }}
                                </span>
                            </div>
                            <div>
                                <PageHeader
                                    :title="`${student.first_name} ${student.last_name}`"
                                    :description="`Student ID: ${student.student_id}`"
                                />
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ enrollments.length }} enrollment{{ enrollments.length !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- GPA Summary -->
                        <div class="flex-shrink-0 rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-center min-w-[120px]">
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ cumulativeGpa !== null && cumulativeGpa !== undefined ? Number(cumulativeGpa).toFixed(2) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cumulative GPA</p>
                        </div>
                    </div>
                </Card>

                <!-- Enrollments + Grades -->
                <div v-if="enrollments.length === 0" class="text-center py-12">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        This student has no enrollments with graded assessments.
                    </p>
                </div>

                <Card
                    v-for="enrollment in enrollments"
                    :key="enrollment.id"
                >
                    <!-- Class Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-4 gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ enrollment.cohort_course?.course?.name || 'N/A' }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                Class {{ enrollment.cohort_course?.cohort?.class?.class_number ?? '?' }}
                                – Cohort {{ enrollment.cohort_course?.cohort?.name ? enrollment.cohort_course.cohort.name.charAt(0).toUpperCase() + enrollment.cohort_course.cohort.name.slice(1) : '' }}
                                <span v-if="enrollment.cohort_course?.employee">
                                    · {{ enrollment.cohort_course.employee.first_name }} {{ enrollment.cohort_course.employee.last_name }}
                                </span>
                                <span v-else-if="enrollment.cohort_course?.institution">
                                    · {{ enrollment.cohort_course.institution.name }}
                                </span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span
                                :class="getStatusBadgeClass(enrollment.status)"
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                            >
                                {{ enrollment.status }}
                            </span>
                            <div class="text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ enrollment.weighted_average !== null && enrollment.weighted_average !== undefined
                                        ? Number(enrollment.weighted_average).toFixed(1) + '%'
                                        : '—' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Average</p>
                            </div>
                            <div class="text-center">
                                <span
                                    v-if="enrollment.final_letter_grade"
                                    :class="getLetterGradeBadgeClass(enrollment.final_letter_grade)"
                                    class="inline-flex rounded-full px-3 py-1 text-sm font-bold"
                                >
                                    {{ enrollment.final_letter_grade }}
                                </span>
                                <span v-else class="text-sm text-gray-400">—</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Grade</p>
                            </div>
                            <div v-if="enrollment.gpa_points !== null && enrollment.gpa_points !== undefined" class="text-center">
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ Number(enrollment.gpa_points).toFixed(1) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">GPA pts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Grades Table -->
                    <div v-if="enrollment.grades && enrollment.grades.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assessment</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Score</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Percentage</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Late</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="grade in enrollment.grades" :key="grade.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ grade.assessment?.name || '—' }}
                                        </div>
                                        <div v-if="grade.assessment?.is_extra_credit" class="text-xs text-yellow-600 dark:text-yellow-400">
                                            Extra Credit
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ grade.assessment?.assessment_category?.name || '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-gray-100">
                                        {{ grade.score }}/{{ grade.assessment?.max_score ?? '?' }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                        {{
                                            grade.assessment?.max_score
                                                ? ((grade.score / grade.assessment.max_score) * 100).toFixed(1) + '%'
                                                : '—'
                                        }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="grade.is_late" class="inline-flex rounded-full bg-yellow-100 dark:bg-yellow-900 px-2 text-xs font-semibold leading-5 text-yellow-800 dark:text-yellow-300">
                                            Late
                                        </span>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs">
                                        <span class="truncate block">{{ grade.notes || '—' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="py-4 text-sm text-gray-500 dark:text-gray-400">
                        No grades recorded for this class.
                    </div>
                </Card>

                <!-- Back Link -->
                <div>
                    <Link
                        :href="route('admin.students.show', student.id)"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Student Profile
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
