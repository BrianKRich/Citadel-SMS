<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student:         Object,
    currentClass:    Object,
    enrollments:     Array,
    classGpa:        Number,
    cumulativeGpa:   Number,
    classes:         Array,
    selectedClassId: Number,
});

const classId = ref(props.selectedClassId);

function changeClass() {
    router.get(route('admin.report-cards.show', props.student.id), {
        class_id: classId.value,
    }, { preserveState: false });
}

function classOptionLabel(c) {
    const classNum = c.class_number ?? '';
    const name = c.name ? ` — ${c.name}` : '';
    const year = c.academic_year?.name ?? '';
    return `Class ${classNum}${name}${year ? ` (${year})` : ''}`;
}

function classHeading(c) {
    if (!c) return '';
    const classNum = c.class_number ?? '';
    const name = c.name ? ` — ${c.name}` : '';
    const year = c.academic_year?.name ?? '';
    return `Class ${classNum}${name}${year ? ` (${year})` : ''}`;
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
    <Head :title="`Report Card — ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Report Card
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Report Cards', href: route('admin.report-cards.index') },
                    { label: `${student.first_name} ${student.last_name}` },
                ]" />

                <!-- Student + Class Header -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <PageHeader
                                :title="`${student.first_name} ${student.last_name}`"
                                :description="`Student ID: ${student.student_id}`"
                            />
                            <div class="mt-2 flex flex-wrap gap-2 items-center">
                                <select
                                    v-model="classId"
                                    @change="changeClass"
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                >
                                    <option v-for="c in classes" :key="c.id" :value="c.id">
                                        {{ classOptionLabel(c) }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Download PDF button -->
                        <div class="flex-shrink-0">
                            <a
                                :href="route('admin.report-cards.pdf', { student: student.id, class_id: selectedClassId })"
                                target="_blank"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >
                                Download PDF
                            </a>
                        </div>
                    </div>
                </Card>

                <!-- Grades Table -->
                <Card>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ classHeading(currentClass) }}
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Instructor</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg %</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Grade</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">GPA Pts</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Credits</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                        {{ enrollment.class_course?.course?.course_code || '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                        {{ enrollment.class_course?.course?.name || '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        <span v-if="enrollment.class_course?.employee">
                                            {{ enrollment.class_course.employee.first_name }} {{ enrollment.class_course.employee.last_name }}
                                        </span>
                                        <span v-else-if="enrollment.class_course?.institution">
                                            {{ enrollment.class_course.institution.name }}
                                        </span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                                        {{ enrollment.weighted_average !== null && enrollment.weighted_average !== undefined
                                            ? Number(enrollment.weighted_average).toFixed(1) + '%'
                                            : '—' }}
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
                                        {{ enrollment.class_course?.course?.credits !== undefined
                                            ? Number(enrollment.class_course.course.credits).toFixed(1)
                                            : '—' }}
                                    </td>
                                </tr>
                                <tr v-if="!enrollments || enrollments.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No enrollments found for this class.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- GPA Summary -->
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 flex gap-8">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ classGpa !== null && classGpa !== undefined ? Number(classGpa).toFixed(2) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Class GPA</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ cumulativeGpa !== null && cumulativeGpa !== undefined ? Number(cumulativeGpa).toFixed(2) : '—' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cumulative GPA</p>
                        </div>
                    </div>
                </Card>

                <!-- Navigation -->
                <div class="flex justify-between text-sm">
                    <Link
                        :href="route('admin.report-cards.index')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Report Cards
                    </Link>
                    <Link
                        :href="route('admin.grades.student', student.id)"
                        class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                    >
                        View Full Grade History →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
