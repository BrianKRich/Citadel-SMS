<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    classModel: Object,
});

// classModel has: course, section_name, assessments (array), enrollments (with student + grades)
const assessments = computed(() => props.classModel.assessments || []);
const enrollments = computed(() => props.classModel.enrollments || []);

// Build class rank map using standard competition ranking (1,1,3)
const rankMap = computed(() => {
    const sorted = [...enrollments.value]
        .filter(e => e.weighted_average !== null && e.weighted_average !== undefined)
        .sort((a, b) => Number(b.weighted_average) - Number(a.weighted_average));

    const map = {};
    let rank = 0;
    let skip = 0;
    let lastAvg = null;

    sorted.forEach(enrollment => {
        skip++;
        const avg = Number(enrollment.weighted_average);
        if (avg !== lastAvg) {
            rank = skip;
            lastAvg = avg;
        }
        map[enrollment.id] = rank;
    });

    return map;
});

// Build a quick-lookup: gradeMap[enrollmentId][assessmentId] = grade
const gradeMap = computed(() => {
    const map = {};
    enrollments.value.forEach(enrollment => {
        map[enrollment.id] = {};
        (enrollment.grades || []).forEach(grade => {
            map[enrollment.id][grade.assessment_id] = grade;
        });
    });
    return map;
});

function getGrade(enrollmentId, assessmentId) {
    return gradeMap.value[enrollmentId]?.[assessmentId] ?? null;
}

function formatScore(grade, assessment) {
    if (!grade) return '—';
    return `${grade.score}/${assessment.max_score}`;
}

function getScoreClass(grade, assessment) {
    if (!grade) return 'text-gray-400 dark:text-gray-600';
    const pct = assessment.max_score > 0 ? (grade.score / assessment.max_score) * 100 : 0;
    if (pct >= 90) return 'text-green-700 dark:text-green-400 font-medium';
    if (pct >= 70) return 'text-blue-700 dark:text-blue-400';
    if (pct >= 60) return 'text-yellow-700 dark:text-yellow-400';
    return 'text-red-700 dark:text-red-400';
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
    <Head :title="`Grade Book — ${classModel.course?.name || 'Class'} ${classModel.section_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Grade Book
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Grade Management', href: route('admin.grades.index') },
                    { label: 'Class Grades' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4 max-w-7xl mx-auto">
                    <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 p-4">
                        <p class="text-sm text-green-800 dark:text-green-300">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <!-- Class Info Header -->
                <Card class="mb-6 max-w-7xl mx-auto">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <PageHeader
                            :title="`${classModel.course?.name || 'N/A'} — ${classModel.section_name}`"
                            :description="`Grade matrix for ${enrollments.length} enrolled students across ${assessments.length} assessments`"
                        />
                        <div class="flex gap-2 flex-shrink-0">
                            <Link
                                :href="route('admin.assessments.create')"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                + Assessment
                            </Link>
                            <Link
                                :href="route('admin.grades.index')"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                All Classes
                            </Link>
                        </div>
                    </div>
                </Card>

                <!-- Grade Matrix -->
                <Card>
                    <div v-if="assessments.length === 0 || enrollments.length === 0" class="py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="assessments.length === 0">No assessments have been created for this class yet. </span>
                            <span v-if="enrollments.length === 0">No students are enrolled in this class yet. </span>
                        </p>
                        <div class="mt-4 flex justify-center gap-3">
                            <Link
                                v-if="assessments.length === 0"
                                :href="route('admin.assessments.create')"
                                class="rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                            >
                                Create Assessment
                            </Link>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto -mx-6 -mb-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <!-- Student column -->
                                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 min-w-[200px] border-r border-gray-200 dark:border-gray-700">
                                        Student
                                    </th>
                                    <!-- Assessment columns -->
                                    <th
                                        v-for="assessment in assessments"
                                        :key="assessment.id"
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 min-w-[100px] border-r border-gray-100 dark:border-gray-700"
                                    >
                                        <div class="font-medium text-gray-700 dark:text-gray-300 truncate max-w-[90px]" :title="assessment.name">
                                            {{ assessment.name }}
                                        </div>
                                        <div class="text-gray-400 dark:text-gray-500 mt-0.5">
                                            /{{ assessment.max_score }}
                                        </div>
                                        <Link
                                            :href="route('admin.grades.enter', assessment.id)"
                                            class="block mt-1 text-primary-600 dark:text-primary-400 hover:underline text-xs normal-case font-normal"
                                        >
                                            Grade
                                        </Link>
                                    </th>
                                    <!-- Summary columns -->
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 min-w-[80px]">
                                        Avg %
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 min-w-[70px]">
                                        Grade
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 min-w-[60px]">
                                        Rank
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <!-- Student Name -->
                                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-6 py-4 border-r border-gray-200 dark:border-gray-700">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ enrollment.student?.first_name }} {{ enrollment.student?.last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ enrollment.student?.student_id }}
                                        </div>
                                    </td>
                                    <!-- Scores -->
                                    <td
                                        v-for="assessment in assessments"
                                        :key="assessment.id"
                                        class="px-4 py-4 text-center border-r border-gray-100 dark:border-gray-700"
                                    >
                                        <span :class="getScoreClass(getGrade(enrollment.id, assessment.id), assessment)" class="text-sm">
                                            {{ formatScore(getGrade(enrollment.id, assessment.id), assessment) }}
                                        </span>
                                        <span
                                            v-if="getGrade(enrollment.id, assessment.id)?.is_late"
                                            class="ml-1 text-xs text-yellow-500 dark:text-yellow-400"
                                            title="Late submission"
                                        >L</span>
                                    </td>
                                    <!-- Weighted Average -->
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ enrollment.weighted_average !== undefined && enrollment.weighted_average !== null
                                                ? Number(enrollment.weighted_average).toFixed(1) + '%'
                                                : '—' }}
                                        </span>
                                    </td>
                                    <!-- Final Grade -->
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            v-if="enrollment.final_letter_grade"
                                            :class="getLetterGradeBadgeClass(enrollment.final_letter_grade)"
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        >
                                            {{ enrollment.final_letter_grade }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <!-- Rank -->
                                    <td class="px-4 py-4 text-center">
                                        <span v-if="rankMap[enrollment.id]" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ rankMap[enrollment.id] }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Back Link -->
                <div class="mt-6 max-w-7xl mx-auto">
                    <Link
                        :href="route('admin.grades.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Grade Book
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
