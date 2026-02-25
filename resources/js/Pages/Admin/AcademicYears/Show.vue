<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    academicYear: Object,
});

function setCurrent() {
    router.post(route('admin.academic-years.set-current', props.academicYear.id));
}

function destroyYear() {
    if (confirm('Delete this academic year?')) {
        router.delete(route('admin.academic-years.destroy', props.academicYear.id));
    }
}

const getStatusBadgeClass = (status) => {
    const map = {
        forming: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[status] || map.forming;
};
</script>

<template>
    <Head :title="`Academic Year: ${academicYear.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Academic Year Details
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academic Years', href: route('admin.academic-years.index') },
                    { label: academicYear.name },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Academic Year Info Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <PageHeader :title="academicYear.name" />
                                <span
                                    v-if="academicYear.is_current"
                                    class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 font-medium"
                                >
                                    Current
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-3 flex-wrap flex-shrink-0">
                            <button
                                v-if="!academicYear.is_current"
                                @click="setCurrent"
                                class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700"
                            >
                                Set as Current
                            </button>
                            <Link
                                :href="route('admin.academic-years.edit', academicYear.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroyYear"
                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(academicYear.start_date).toLocaleDateString() }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(academicYear.end_date).toLocaleDateString() }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Classes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ academicYear.classes?.length ?? 0 }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Classes Card -->
                <Card>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Classes</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Classes assigned to this academic year</p>
                        </div>
                        <Link
                            :href="route('admin.classes.create')"
                            class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700"
                        >
                            + Add Class
                        </Link>
                    </div>

                    <div v-if="academicYear.classes?.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">NGB Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Courses</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="cls in academicYear.classes" :key="cls.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Class {{ cls.class_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.ngb_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="getStatusBadgeClass(cls.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                            {{ cls.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.class_courses_count ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.classes.show', cls.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >View</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No classes are associated with this academic year.
                    </div>
                </Card>

                <!-- Back Link -->
                <div class="px-4 sm:px-0">
                    <Link
                        :href="route('admin.academic-years.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Academic Years
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
