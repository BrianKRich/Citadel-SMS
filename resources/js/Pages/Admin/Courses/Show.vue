<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    course: Object,
    customFields: { type: Array, default: () => [] },
});

function destroy() {
    if (confirm(`Are you sure you want to delete "${props.course.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.courses.destroy', props.course.id));
    }
}
</script>

<template>
    <Head :title="`${course.course_code} — ${course.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Course Detail</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Courses', href: route('admin.courses.index') },
                    { label: course.name },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Card 1: Course Information -->
                <Card class="mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <PageHeader
                                :title="course.name"
                                :description="course.course_code"
                            />
                        </div>
                        <div class="flex gap-3 ml-4 flex-shrink-0">
                            <Link
                                :href="route('admin.courses.edit', course.id)"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700"
                            >Edit Course</Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20"
                            >Delete</button>
                        </div>
                    </div>

                    <!-- Active Badge -->
                    <div class="mt-3">
                        <span
                            :class="course.is_active
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                        >{{ course.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>

                    <!-- Definition List -->
                    <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.course_code }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Department</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.department || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Level</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.level || '—' }}</dd>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grading</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ course.grading_type === 'pass_fail' ? 'Pass / Fail' : ('Credit System' + (course.credits ? ' — ' + course.credits + ' credits' : '')) }}
                            </dd>
                        </div>

                        <div v-if="course.description" class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:col-span-2 lg:col-span-3">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.description }}</dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Classes Using This Course -->
                <Card>
                    <PageHeader title="Classes Using This Course" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Instructor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enrolled / Capacity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr
                                    v-for="cc in course.class_courses"
                                    :key="cc.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ cc.class?.name ?? ('Class ' + (cc.class?.class_number ?? '—')) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <span v-if="cc.employee">{{ cc.employee.first_name }} {{ cc.employee.last_name }}</span>
                                        <span v-else-if="cc.institution">{{ cc.institution.name }}</span>
                                        <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ cc.enrollments_count ?? '—' }} / {{ cc.max_students ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ cc.status?.replace('_', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <Link
                                            :href="route('admin.class-courses.show', cc.id)"
                                            class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                        >View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!course.class_courses?.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No course assignments scheduled for this course.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Custom Fields -->
                <Card v-if="customFields.length" class="mt-6">
                    <CustomFieldsSection :fields="customFields" :readonly="true" />
                </Card>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <Link
                        :href="route('admin.courses.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >← Back to Courses</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
