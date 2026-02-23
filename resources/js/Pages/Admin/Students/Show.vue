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
    student: Object,
    customFields: { type: Array, default: () => [] },
});

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        graduated: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        suspended: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function getEnrollmentStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        dropped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
}

function formatAddress() {
    const parts = [props.student.city, props.student.state, props.student.postal_code].filter(Boolean);
    return parts.length ? parts.join(', ') : '—';
}

function destroy() {
    if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        router.delete(route('admin.students.destroy', props.student.id));
    }
}
</script>

<template>
    <Head :title="`Student: ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Student Details</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Students', href: route('admin.students.index') },
                    { label: `${student.first_name} ${student.last_name}` },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Soft-deleted warning -->
                <div v-if="student.deleted_at" class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4">
                    <p class="text-sm text-red-800 dark:text-red-300 font-medium">
                        This student record has been deleted and is no longer active.
                        Deleted on {{ formatDate(student.deleted_at) }}.
                    </p>
                </div>

                <!-- Card 1: Student Profile -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Photo or Initials Avatar -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="student.photo"
                                    :src="'/storage/' + student.photo"
                                    class="h-24 w-24 rounded-full object-cover"
                                    :alt="`${student.first_name} ${student.last_name}`"
                                />
                                <div
                                    v-else
                                    class="h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                                >
                                    <span class="text-2xl text-gray-500 dark:text-gray-400">{{ student.first_name?.[0] }}</span>
                                </div>
                            </div>

                            <div>
                                <PageHeader
                                    :title="`${student.first_name}${student.middle_name ? ' ' + student.middle_name : ''} ${student.last_name}`"
                                    :description="student.student_id"
                                />
                                <div class="mt-2">
                                    <span
                                        :class="getStatusBadgeClass(student.status)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize"
                                    >
                                        {{ student.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 flex-shrink-0 flex-wrap">
                            <Link
                                v-if="$page.props.features?.attendance_enabled"
                                :href="route('admin.attendance.student', student.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                View Attendance
                            </Link>
                            <Link
                                :href="route('admin.students.edit', student.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center rounded-md border border-red-300 dark:border-red-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(student.date_of_birth) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Gender</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ student.gender || '—' }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a v-if="student.email" :href="`mailto:${student.email}`" class="text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ student.email }}
                                </a>
                                <span v-else>—</span>
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enrollment Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(student.enrollment_date) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <div v-if="student.address">{{ student.address }}</div>
                                <div>{{ formatAddress() }}</div>
                                <span v-if="!student.address && !student.city && !student.state && !student.postal_code">—</span>
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Emergency Contact</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <div v-if="student.emergency_contact_name">{{ student.emergency_contact_name }}</div>
                                <div v-if="student.emergency_contact_phone" class="text-gray-500 dark:text-gray-400">
                                    {{ student.emergency_contact_phone }}
                                </div>
                                <span v-if="!student.emergency_contact_name && !student.emergency_contact_phone">—</span>
                            </dd>
                        </div>
                        <div v-if="student.notes" class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:col-span-2 lg:col-span-3">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ student.notes }}</dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Guardians -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Guardians</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Parent and guardian contact information</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Relationship</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Primary</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="guardian in student.guardians" :key="guardian.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ guardian.first_name }} {{ guardian.last_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ guardian.relationship || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ guardian.phone || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="guardian.is_primary"
                                            class="inline-flex rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 px-2 text-xs font-semibold leading-5"
                                        >
                                            Primary
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                </tr>
                                <tr v-if="!student.guardians?.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No guardians found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Card 3: Enrollment History -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Enrollment History</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Courses this student has been enrolled in</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Term</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="enrollment in student.enrollments" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ enrollment.class?.course?.course_code || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.class?.course?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ enrollment.class?.term?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getEnrollmentStatusBadgeClass(enrollment.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >
                                            {{ enrollment.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.final_letter_grade || '—' }}
                                    </td>
                                </tr>
                                <tr v-if="!student.enrollments?.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No enrollments found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Custom Fields -->
                <Card v-if="customFields.length">
                    <CustomFieldsSection :fields="customFields" :readonly="true" />
                </Card>

                <!-- Back Link -->
                <div class="px-4 sm:px-0">
                    <Link
                        :href="route('admin.students.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Students
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
