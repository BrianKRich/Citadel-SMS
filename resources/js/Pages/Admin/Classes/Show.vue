<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const cls = computed(() => page.props.class);

const getClassStatusBadge = (s) => {
    const map = {
        forming:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        active:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.forming;
};

const formatDate = (d) => d ? d.substring(0, 10) : '—';

function destroy() {
    if (confirm('Delete this class? This cannot be undone.')) {
        router.delete(route('admin.classes.destroy', cls.value.id));
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
                    { label: 'Class Management', href: route('admin.class-layout.index') },
                    { label: 'Class Setup', href: route('admin.classes.index') },
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
                            <PageHeader :title="`Class ${cls?.class_number}`" description="Class details." />
                            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Class Name</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ cls?.name ?? '—' }}</dd>
                                </div>
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
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ formatDate(cls?.start_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ formatDate(cls?.end_date) }}</dd>
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

                <!-- Course Assignments Card -->
                <Card>
                    <div class="flex items-center justify-between mb-4">
                        <PageHeader title="Course Assignments" description="Courses assigned to this class." />
                        <Link
                            :href="route('admin.class-courses.create') + '?class_id=' + cls?.id"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                        >+ Add Course</Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Instructor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Capacity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr
                                    v-for="cc in cls?.class_courses"
                                    :key="cc.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ cc.course?.name ?? '—' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ cc.course?.course_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <span v-if="cc.employee">{{ cc.employee.first_name }} {{ cc.employee.last_name }}</span>
                                        <span v-else-if="cc.institution">{{ cc.institution.name }}</span>
                                        <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ cc.room || '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ cc.max_students ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 capitalize">{{ cc.status?.replace('_', ' ') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <Link
                                            :href="route('admin.class-courses.show', cc.id)"
                                            class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                        >View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!cls?.class_courses?.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No courses assigned to this class yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
