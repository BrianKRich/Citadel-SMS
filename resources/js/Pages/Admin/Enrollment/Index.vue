<template>
    <Head title="Enrollments" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="$page.props.flash?.success" class="mb-4">
                <Alert type="success" :message="$page.props.flash.success" />
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4">
                <Alert type="error" :message="$page.props.flash.error" />
            </div>

            <Card>
                <div class="p-6">
                    <PageHeader title="Enrollments">
                        <template #actions>
                            <Link
                                :href="route('admin.enrollment.create')"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md shadow-sm transition"
                            >
                                Enroll Student
                            </Link>
                        </template>
                    </PageHeader>

                    <!-- Filters -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Name, student ID, course..."
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Class</label>
                            <select
                                v-model="selectedClass"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Classes</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    Class {{ cls.class_number }} ({{ cls.academic_year?.name }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Assignment</label>
                            <select
                                v-model="selectedClassCourse"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Course Assignments</option>
                                <option v-for="cc in classCoursesForFilter" :key="cc.id" :value="cc.id">
                                    Class {{ cc.class?.class_number }} / {{ cc.course?.course_code }} — {{ cc.course?.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Statuses</option>
                                <option value="enrolled">Enrolled</option>
                                <option value="dropped">Dropped</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Summary -->
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ enrollments.total }} enrollment(s) found
                    </p>

                    <!-- Desktop Table -->
                    <div class="mt-4 hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Student ID
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Student Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Course
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Class
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Enrolled Date
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="enrollments.data?.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No enrollments found.
                                    </td>
                                </tr>
                                <tr
                                    v-for="enrollment in enrollments.data"
                                    :key="enrollment.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                >
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">
                                        {{ enrollment.student?.student_id }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.student?.first_name }} {{ enrollment.student?.last_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <span class="font-medium">{{ enrollment.classCourse?.course?.course_code }}</span>
                                        — {{ enrollment.classCourse?.course?.name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        Class {{ enrollment.classCourse?.class?.class_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="getStatusBadgeClass(enrollment.status)"
                                        >
                                            {{ enrollment.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ fmtDate(enrollment.enrollment_date) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center gap-3">
                                            <Link
                                                :href="route('admin.enrollment.student-schedule', enrollment.student?.id)"
                                                class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                            >
                                                View Schedule
                                            </Link>
                                            <button
                                                v-if="enrollment.status === 'enrolled'"
                                                @click="drop(enrollment)"
                                                class="text-xs text-red-600 hover:text-red-700 dark:text-red-400"
                                            >
                                                Drop
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="mt-4 block sm:hidden space-y-4">
                        <div
                            v-if="enrollments.data?.length === 0"
                            class="py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                        >
                            No enrollments found.
                        </div>
                        <div
                            v-for="enrollment in enrollments.data"
                            :key="enrollment.id"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 space-y-2"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ enrollment.student?.first_name }} {{ enrollment.student?.last_name }}
                                    </p>
                                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                        {{ enrollment.student?.student_id }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize shrink-0"
                                    :class="getStatusBadgeClass(enrollment.status)"
                                >
                                    {{ enrollment.status }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ enrollment.classCourse?.course?.course_code }}</span>
                                — {{ enrollment.classCourse?.course?.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Class {{ enrollment.classCourse?.class?.class_number ?? '—' }}
                                &bull; {{ enrollment.classCourse?.employee?.first_name }} {{ enrollment.classCourse?.employee?.last_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Enrolled: {{ fmtDate(enrollment.enrollment_date) }}
                            </p>
                            <div class="flex items-center gap-4 pt-1">
                                <Link
                                    :href="route('admin.enrollment.student-schedule', enrollment.student?.id)"
                                    class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                >
                                    View Schedule
                                </Link>
                                <button
                                    v-if="enrollment.status === 'enrolled'"
                                    @click="drop(enrollment)"
                                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400"
                                >
                                    Drop
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="enrollments.links?.length > 3" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ enrollments.from }}–{{ enrollments.to }} of {{ enrollments.total }}
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in enrollments.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 text-sm rounded-md"
                                    :class="link.active ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    enrollments: Object,
    students: Array,
    classes: Array,
    filters: Object,
});

function fmtDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.substring(0, 10).split('-');
    return `${m}-${d}-${y}`;
}

const search = ref(props.filters?.search ?? '');
const selectedClass = ref(props.filters?.class_id ?? '');
const selectedClassCourse = ref(props.filters?.class_course_id ?? '');
const selectedStatus = ref(props.filters?.status ?? '');

// Build a flat list of class courses from all classes for the filter dropdown
const classCoursesForFilter = computed(() => {
    const list = [];
    for (const cls of (props.classes ?? [])) {
        for (const cc of (cls.class_courses ?? [])) {
            list.push({
                ...cc,
                class: cls,
            });
        }
    }
    return list;
});

function applyFilters() {
    router.get(route('admin.enrollment.index'), {
        search: search.value || undefined,
        class_id: selectedClass.value || undefined,
        class_course_id: selectedClassCourse.value || undefined,
        status: selectedStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
watch([selectedClass, selectedClassCourse, selectedStatus], applyFilters);

function getStatusBadgeClass(status) {
    const c = {
        enrolled: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        dropped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        failed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return c[status] || 'bg-gray-100 text-gray-800';
}

function drop(enrollment) {
    if (confirm(`Drop ${enrollment.student?.first_name} from this course assignment?`)) {
        router.delete(route('admin.enrollment.drop', enrollment.id));
    }
}
</script>
