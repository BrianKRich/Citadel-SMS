<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    cohortCourses: { type: Object, required: true },
    courses:       { type: Array, default: () => [] },
    filters:       { type: Object, default: () => ({}) },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let debounceTimer = null;

function applyFilters() {
    router.get(route('admin.cohort-courses.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});

watch(status, applyFilters);

const getStatusBadge = (s) => {
    const map = {
        open:        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed:      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed:   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.open;
};

const cohortLabel = (cc) => {
    const name = cc.cohort?.name;
    if (name === 'alpha') return 'Cohort Alpha';
    if (name === 'bravo') return 'Cohort Bravo';
    return name ?? '—';
};

const instructorLabel = (cc) => {
    if (cc.instructor_type === 'staff' && cc.employee) {
        return `${cc.employee.first_name} ${cc.employee.last_name}`;
    }
    return cc.institution?.name ?? '—';
};
</script>

<template>
    <Head title="Course Assignments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Course Assignments</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Course Assignments' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Course Assignments"
                            :description="`${cohortCourses.total} assignment${cohortCourses.total === 1 ? '' : 's'} total`"
                        />
                        <div class="sm:ml-auto">
                            <Link
                                :href="route('admin.cohort-courses.create')"
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >+ Add Course Assignment</Link>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search courses, classes..."
                                :class="inputClass"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="status" :class="inputClass">
                                <option value="">All Statuses</option>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cohort</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Instructor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Enrolled / Max</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-if="cohortCourses.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No assignments found.</td>
                                </tr>
                                <tr
                                    v-for="cc in cohortCourses.data"
                                    :key="cc.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ cc.course?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        Class {{ cc.cohort?.class?.class_number ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cohortLabel(cc) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ instructorLabel(cc) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="getStatusBadge(cc.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                            {{ cc.status?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cc.enrolled_count ?? 0 }} / {{ cc.max_students ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-4">
                                        <Link :href="route('admin.cohort-courses.show', cc.id)" class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">View</Link>
                                        <Link :href="route('admin.cohort-courses.edit', cc.id)" class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">Edit</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <div v-if="cohortCourses.data.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">No assignments found.</div>
                        <div
                            v-for="cc in cohortCourses.data"
                            :key="cc.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ cc.course?.name ?? '—' }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Class {{ cc.cohort?.class?.class_number ?? '—' }} — {{ cohortLabel(cc) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ instructorLabel(cc) }}</p>
                                </div>
                                <span :class="getStatusBadge(cc.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                    {{ cc.status?.replace('_', ' ') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Enrolled: {{ cc.enrolled_count ?? 0 }} / {{ cc.max_students ?? '—' }}</p>
                            <div class="flex gap-2">
                                <Link :href="route('admin.cohort-courses.show', cc.id)" class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700 min-h-[44px] flex items-center justify-center">View</Link>
                                <Link :href="route('admin.cohort-courses.edit', cc.id)" class="flex-1 text-center rounded-md bg-secondary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-secondary-700 min-h-[44px] flex items-center justify-center">Edit</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="cohortCourses.links && cohortCourses.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link v-if="cohortCourses.prev_page_url" :href="cohortCourses.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50">Previous</Link>
                            <Link v-if="cohortCourses.next_page_url" :href="cohortCourses.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50">Next</Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing <span class="font-medium">{{ cohortCourses.from }}</span> to <span class="font-medium">{{ cohortCourses.to }}</span> of <span class="font-medium">{{ cohortCourses.total }}</span> assignments
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in cohortCourses.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    :class="[
                                        link.active ? 'z-10 bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        !link.url ? 'pointer-events-none opacity-50' : '',
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
