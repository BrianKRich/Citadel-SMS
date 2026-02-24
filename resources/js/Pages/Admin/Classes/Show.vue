<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';

import { ref, computed } from 'vue';

const page = usePage();
const cls = computed(() => page.props.class);

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const getClassStatusBadge = (s) => {
    const map = {
        forming:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        active:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.forming;
};

const getCohortCourseStatusBadge = (s) => {
    const map = {
        open:        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed:      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed:   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.open;
};

const getCohort = (name) => (cls.value?.cohorts ?? []).find(c => c.name === name);

// Per-cohort date editing state
const cohortDateForms = ref({});

function getCohortDateForm(cohort) {
    if (!cohortDateForms.value[cohort.id]) {
        cohortDateForms.value[cohort.id] = useForm({
            start_date: cohort.start_date ? cohort.start_date.substring(0, 10) : '',
            end_date:   cohort.end_date   ? cohort.end_date.substring(0, 10)   : '',
        });
    }
    return cohortDateForms.value[cohort.id];
}

function saveCohortDates(cohort) {
    const form = getCohortDateForm(cohort);
    form.patch(route('admin.classes.cohorts.update', [cls.value.id, cohort.id]));
}

function destroy() {
    if (confirm('Delete this class? This cannot be undone.')) {
        router.delete(route('admin.classes.destroy', cls.value.id));
    }
}

const instructorLabel = (cc) => {
    if (cc.instructor_type === 'staff' && cc.employee) {
        return `${cc.employee.first_name} ${cc.employee.last_name}`;
    }
    return cc.institution?.name ?? '—';
};

const cohortTitle = (name) => name === 'alpha' ? 'Cohort Alpha' : 'Cohort Bravo';

const cohortView = ref('');

function removeCourse(cc) {
    if (confirm(`Remove "${cc.course?.name}" from this cohort? This cannot be undone.`)) {
        router.delete(route('admin.cohort-courses.destroy', cc.id), {
            data: { from: 'class' },
        });
    }
}
</script>

<template>
    <Head :title="`Class ${cls?.class_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Class {{ cls?.class_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Class Management', href: route('admin.classes.index') },
                    { label: `Class ${cls?.class_number}` },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Class Info Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <PageHeader :title="`Class ${cls?.class_number}`" description="Class details and cohort management." />
                            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">NGB Number</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ cls?.ngb_number ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Academic Year</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ cls?.academic_year?.name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span :class="getClassStatusBadge(cls?.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize">
                                            {{ cls?.status }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.classes.edit', cls?.id)"
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

                <!-- Cohort Filter -->
                <div class="flex items-center gap-3">
                    <label for="cohort_view" class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">View:</label>
                    <select
                        id="cohort_view"
                        v-model="cohortView"
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                    >
                        <option value="" disabled>Select a cohort...</option>
                        <option value="both">Both Cohorts</option>
                        <option value="alpha">Cohort Alpha</option>
                        <option value="bravo">Cohort Bravo</option>
                    </select>
                </div>

                <!-- Cohort Cards -->
                <template v-for="cohortName in ['alpha', 'bravo']" :key="cohortName">
                    <Card v-if="cohortView && getCohort(cohortName) && (cohortView === 'both' || cohortView === cohortName)">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ cohortTitle(cohortName) }}
                            </h3>
                        </div>

                        <!-- Inline Date Edit Form -->
                        <div class="mb-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Cohort Dates</h4>
                            <form @submit.prevent="saveCohortDates(getCohort(cohortName))" class="flex flex-col sm:flex-row sm:items-end gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Start Date</label>
                                    <input
                                        type="date"
                                        v-model="getCohortDateForm(getCohort(cohortName)).start_date"
                                        :class="inputClass"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">End Date</label>
                                    <input
                                        type="date"
                                        v-model="getCohortDateForm(getCohort(cohortName)).end_date"
                                        :class="inputClass"
                                    />
                                </div>
                                <div>
                                    <PrimaryButton
                                        type="submit"
                                        :disabled="getCohortDateForm(getCohort(cohortName)).processing"
                                    >
                                        {{ getCohortDateForm(getCohort(cohortName)).processing ? 'Saving...' : 'Save Dates' }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Cohort Courses Table -->
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Courses</h4>
                            <Link
                                :href="route('admin.cohort-courses.create') + '?cohort_id=' + getCohort(cohortName).id"
                                class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700"
                            >+ Add Course to {{ cohortTitle(cohortName) }}</Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Instructor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Room</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Enrolled / Max</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-if="!getCohort(cohortName).cohort_courses || getCohort(cohortName).cohort_courses.length === 0">
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No courses assigned yet.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="cc in getCohort(cohortName).cohort_courses"
                                        :key="cc.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ cc.course?.name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ instructorLabel(cc) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ cc.room ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="getCohortCourseStatusBadge(cc.status)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >{{ cc.status?.replace('_', ' ') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ cc.enrolled_count ?? 0 }} / {{ cc.max_students ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium space-x-3">
                                            <Link
                                                :href="route('admin.cohort-courses.show', cc.id)"
                                                class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                            >View</Link>
                                            <Link
                                                :href="route('admin.cohort-courses.edit', cc.id)"
                                                class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                            >Edit</Link>
                                            <button
                                                type="button"
                                                @click="removeCourse(cc)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                            >Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
