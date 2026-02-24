<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cohortCourses: Object,
    filters: Object,
});

const showSuccess = ref(!!props.cohortCourses.flash?.success);
const showError = ref(!!props.cohortCourses.flash?.error);

const search = ref(props.filters?.search || '');

function applySearch() {
    router.get(route('admin.grades.index'), {
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
}

function getStatusBadgeClass(status) {
    const classes = {
        open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return classes[status] || classes.open;
}

function cohortLabel(cc) {
    const cohortName = cc.cohort?.name ? cc.cohort.name.charAt(0).toUpperCase() + cc.cohort.name.slice(1) : '';
    const classNum = cc.cohort?.class?.class_number ?? '';
    return classNum ? `Class ${classNum} – Cohort ${cohortName}` : cohortName;
}

function instructorLabel(cc) {
    if (cc.instructor_type === 'staff' && cc.employee) {
        return `${cc.employee.first_name} ${cc.employee.last_name}`;
    }
    if (cc.institution) {
        return cc.institution.name;
    }
    return cc.instructor_type?.replace('_', ' ') ?? 'N/A';
}
</script>

<template>
    <Head title="Grade Book" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Grade Book
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Alerts -->
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert
                        type="success"
                        :message="$page.props.flash.success"
                        @dismiss="showSuccess = false"
                    />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert
                        type="error"
                        :message="$page.props.flash.error"
                        @dismiss="showError = false"
                    />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Grade Book"
                            :description="`Select a cohort course to view or enter grades. ${cohortCourses.total} courses total.`"
                        />
                    </div>

                    <!-- Search -->
                    <div class="mb-4 flex gap-3">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search courses..."
                            @keyup.enter="applySearch"
                            class="block w-full max-w-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                        <button
                            @click="applySearch"
                            class="rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                        >
                            Search
                        </button>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class / Cohort</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Instructor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Enrolled</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assessments</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="cc in cohortCourses.data" :key="cc.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ cc.course?.name || 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ cc.course?.course_code || '' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ cohortLabel(cc) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ instructorLabel(cc) }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(cc.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{ cc.status?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ cc.enrollments_count ?? 0 }} students
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ cc.assessments_count ?? 0 }} assessments
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.grades.class', cc.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >
                                            View Grade Book
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="cohortCourses.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No cohort courses found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="cc in cohortCourses.data"
                            :key="cc.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ cc.course?.name || 'N/A' }} — {{ cohortLabel(cc) }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ instructorLabel(cc) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ cc.enrollments_count ?? 0 }} students • {{ cc.assessments_count ?? 0 }} assessments
                                    </p>
                                </div>
                                <span
                                    :class="getStatusBadgeClass(cc.status)"
                                    class="ml-2 inline-flex flex-shrink-0 rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ cc.status?.replace('_', ' ') }}
                                </span>
                            </div>
                            <div class="mt-3">
                                <Link
                                    :href="route('admin.grades.class', cc.id)"
                                    class="block text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center"
                                >
                                    View Grade Book
                                </Link>
                            </div>
                        </div>
                        <div v-if="cohortCourses.data.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No cohort courses found.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="cohortCourses.links && cohortCourses.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="cohortCourses.prev_page_url"
                                :href="cohortCourses.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="cohortCourses.next_page_url"
                                :href="cohortCourses.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ cohortCourses.from }}</span>
                                to
                                <span class="font-medium">{{ cohortCourses.to }}</span>
                                of
                                <span class="font-medium">{{ cohortCourses.total }}</span>
                                cohort courses
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in cohortCourses.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-primary-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 dark:ring-gray-600',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
