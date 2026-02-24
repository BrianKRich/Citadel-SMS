<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    official: Boolean,
    cohortGroups: Array,
    totalCredits: Number,
    cumulativeGpa: Number,
});

function cohortHeading(cohort) {
    if (!cohort) return '';
    const name = cohort.name ? cohort.name.charAt(0).toUpperCase() + cohort.name.slice(1) : '';
    const classNum = cohort.class?.class_number ?? '';
    const year = cohort.class?.academic_year?.name ?? '';
    return `Cohort ${name}${classNum ? ` – Class ${classNum}` : ''}${year ? ` (${year})` : ''}`;
}

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
</script>

<template>
    <Head :title="`Transcript — ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Transcript
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Transcripts', href: route('admin.transcripts.index') },
                    { label: `${student.first_name} ${student.last_name}` },
                ]" />

                <!-- Student Header -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <PageHeader
                                :title="`${student.first_name} ${student.last_name}`"
                                :description="`Student ID: ${student.student_id}`"
                            />
                            <div v-if="official" class="mt-2">
                                <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-3 py-1 text-xs font-semibold text-blue-800 dark:text-blue-300">
                                    OFFICIAL TRANSCRIPT
                                </span>
                            </div>
                            <div v-else class="mt-2">
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    UNOFFICIAL TRANSCRIPT
                                </span>
                            </div>
                        </div>

                        <!-- Download buttons -->
                        <div class="flex-shrink-0 flex flex-col sm:flex-row gap-2">
                            <a
                                :href="route('admin.transcripts.pdf', student.id)"
                                target="_blank"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Unofficial PDF
                            </a>
                            <a
                                :href="route('admin.transcripts.pdf', { student: student.id, official: 1 })"
                                target="_blank"
                                class="inline-flex items-center justify-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >
                                Official PDF
                            </a>
                        </div>
                    </div>
                </Card>

                <!-- Cohort Blocks -->
                <div v-if="cohortGroups && cohortGroups.length > 0" class="space-y-4">
                    <Card v-for="group in cohortGroups" :key="group.cohort?.id">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">
                            {{ cohortHeading(group.cohort) }}
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Name</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Grade</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">GPA Pts</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Credits</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-for="enrollment in group.enrollments" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                            {{ enrollment.cohort_course?.course?.course_code || '—' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ enrollment.cohort_course?.course?.name || '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                v-if="enrollment.final_letter_grade"
                                                :class="getLetterGradeBadgeClass(enrollment.final_letter_grade)"
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold"
                                            >
                                                {{ enrollment.final_letter_grade }}
                                            </span>
                                            <span v-else class="text-gray-400">—</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                                            {{ enrollment.grade_points !== null && enrollment.grade_points !== undefined
                                                ? Number(enrollment.grade_points).toFixed(2)
                                                : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                                            {{ enrollment.cohort_course?.course?.credits !== undefined
                                                ? Number(enrollment.cohort_course.course.credits).toFixed(1)
                                                : '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Cohort Footer -->
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex gap-6 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Cohort GPA:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ Number(group.cohortGpa).toFixed(2) }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Cohort Credits:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ Number(group.cohortCredits).toFixed(1) }}</span>
                            </div>
                        </div>
                    </Card>
                </div>

                <div v-else>
                    <Card>
                        <p class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No academic history found for this student.
                        </p>
                    </Card>
                </div>

                <!-- Cumulative Summary -->
                <Card v-if="cohortGroups && cohortGroups.length > 0">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Cumulative Summary</h3>
                    <div class="flex gap-12">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ totalCredits !== null && totalCredits !== undefined ? Number(totalCredits).toFixed(1) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Credits</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {{ cumulativeGpa !== null && cumulativeGpa !== undefined ? Number(cumulativeGpa).toFixed(2) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cumulative GPA</p>
                        </div>
                    </div>
                </Card>

                <!-- Navigation -->
                <div class="flex justify-between text-sm">
                    <Link
                        :href="route('admin.transcripts.index')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Transcripts
                    </Link>
                    <Link
                        :href="route('admin.grades.student', student.id)"
                        class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                    >
                        View Grade Detail →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
