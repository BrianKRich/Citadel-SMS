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
    employee: Object,
    customFields: { type: Array, default: () => [] },
});

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        on_leave: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function destroy() {
    if (confirm('Are you sure? This will soft-delete the employee.')) {
        router.delete(route('admin.employees.destroy', props.employee.id));
    }
}
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Employee Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Employees', href: route('admin.employees.index') },
                    { label: `${employee.first_name} ${employee.last_name}` },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Card 1: Employee Profile -->
                <Card class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <!-- Photo or Initials -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="employee.photo"
                                    :src="`/storage/${employee.photo}`"
                                    :alt="`${employee.first_name} ${employee.last_name}`"
                                    class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                />
                                <div
                                    v-else
                                    class="h-20 w-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center ring-2 ring-gray-200 dark:ring-gray-700"
                                >
                                    <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                                        {{ employee.first_name.charAt(0) }}{{ employee.last_name.charAt(0) }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ employee.first_name }} {{ employee.last_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ employee.employee_id }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        :class="getStatusBadgeClass(employee.status)"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        {{ employee.status.replace('_', ' ') }}
                                    </span>
                                    <span
                                        v-if="employee.user"
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        Has Login Account
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 flex-shrink-0">
                            <Link
                                :href="route('admin.employees.edit', employee.id)"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Definition List -->
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a :href="`mailto:${employee.email}`" class="text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ employee.email }}
                                </a>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <span v-if="employee.phone_numbers && employee.phone_numbers.length">
                                    ({{ employee.phone_numbers[0].area_code }}) {{ employee.phone_numbers[0].number }}
                                </span>
                                <span v-else class="text-gray-400 dark:text-gray-500">Not provided</span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hire Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.hire_date ? employee.hire_date.substring(0, 10) : 'Not recorded' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.date_of_birth ? employee.date_of_birth.substring(0, 10) : 'Not recorded' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Department</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.department?.name ?? 'Not assigned' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.role?.name ?? 'Not assigned' }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Qualifications -->
                <Card class="mb-6">
                    <PageHeader title="Qualifications" />
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" v-if="employee.qualifications">
                        {{ employee.qualifications }}
                    </p>
                    <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
                        No qualifications recorded.
                    </p>
                </Card>

                <!-- Card 3: Teaching Schedule (only shown when classes are assigned) -->
                <Card v-if="employee.classes && employee.classes.length > 0" class="mb-6">
                    <PageHeader title="Teaching Schedule" description="Classes currently assigned to this employee." />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Course Code
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Course Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Section
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Term
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Enrolled / Capacity
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr
                                    v-for="cls in employee.classes"
                                    :key="cls.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ cls.course?.course_code ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ cls.course?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.section_name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.term?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.enrolled_count }} / {{ cls.max_students }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span
                                            :class="getStatusBadgeClass(cls.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >
                                            {{ cls.status?.replace('_', ' ') ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </Card>

                <!-- Custom Fields -->
                <Card v-if="customFields.length" class="mb-6">
                    <CustomFieldsSection :fields="customFields" :readonly="true" />
                </Card>

                <!-- Back Link -->
                <div class="mt-2">
                    <Link
                        :href="route('admin.employees.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Employees
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
