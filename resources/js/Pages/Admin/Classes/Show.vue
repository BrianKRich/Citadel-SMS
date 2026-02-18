<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const page = usePage();
const cls = computed(() => page.props.class);

const getStatusBadgeClass = (status) => {
    const map = {
        open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[status] || map.open;
};

const getEnrollmentBadgeClass = (status) => {
    const map = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        dropped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return map[status] || map.active;
};

function destroy() {
    if (confirm('Delete this class? This will fail if students are enrolled.')) {
        router.delete(route('admin.classes.destroy', cls.value.id));
    }
}
</script>

<template>
    <Head :title="`${cls?.course?.course_code} — ${cls?.section_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Class Detail</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Card 1: Class Information -->
                <Card class="mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <PageHeader
                                :title="`${cls?.course?.course_code} — ${cls?.section_name}`"
                                description="Class details and enrollment information"
                            />
                        </div>
                        <div class="flex gap-3 ml-4 flex-shrink-0">
                            <Link
                                :href="route('admin.classes.edit', cls.id)"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700"
                            >Edit Class</Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20"
                            >Delete</button>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="mt-3">
                        <span
                            :class="getStatusBadgeClass(cls?.status)"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                        >{{ cls?.status?.replace('_', ' ') }}</span>
                    </div>

                    <!-- Definition List -->
                    <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.course?.name || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teacher</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <span v-if="cls?.employee">{{ cls.employee.first_name }} {{ cls.employee.last_name }}</span>
                                <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                            </dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Academic Year</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.academicYear?.name || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Term</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.term?.name || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Room</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.room || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Max Capacity</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.max_students ?? '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enrolled Count</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ cls?.enrollments?.length ?? 0 }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:col-span-2 lg:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Schedule</dt>
                            <dd v-if="cls?.schedule?.length" class="mt-1">
                                <span
                                    v-for="s in cls.schedule"
                                    :key="s.day"
                                    class="block text-sm text-gray-900 dark:text-gray-100"
                                >
                                    {{ s.day }}: {{ s.start_time }}–{{ s.end_time }}
                                </span>
                            </dd>
                            <dd v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">No schedule set</dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Enrolled Students -->
                <Card>
                    <PageHeader title="Enrolled Students" />

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Student ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Enrolled Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr
                                    v-for="e in cls?.enrollments"
                                    :key="e.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ e.student?.student_id }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ e.student?.first_name }} {{ e.student?.last_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(e.enrollment_date).toLocaleDateString() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getEnrollmentBadgeClass(e.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >{{ e.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="!cls?.enrollments?.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No students currently enrolled.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <Link
                        :href="route('admin.classes.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >&#x2190; Back to Classes</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
