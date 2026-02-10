<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    employees: Object,
    departments: Array,
    filters: Object,
});

const showSuccess = ref(!!props.employees.flash?.success);
const showError = ref(!!props.employees.flash?.error);

const getStatusBadgeClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        on_leave: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || classes.inactive;
};
</script>

<template>
    <Head title="Employee Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Employee Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Alerts -->
                <div v-if="showSuccess" class="mb-4">
                    <Alert
                        type="success"
                        :message="$page.props.flash.success"
                        @dismiss="showSuccess = false"
                    />
                </div>

                <div v-if="showError" class="mb-4">
                    <Alert
                        type="error"
                        :message="$page.props.flash.error"
                        @dismiss="showError = false"
                    />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Employees"
                            :description="`Manage all ${employees.total} employee profiles`"
                        />

                        <div class="sm:ml-auto">
                            <PrimaryButton class="w-full sm:w-auto">
                                + Add New Employee
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Employee ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Department
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="employee in employees.data" :key="employee.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ employee.employee_id }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ employee.first_name }} {{ employee.last_name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ employee.email }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ employee.department?.name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ employee.role?.name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(employee.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{ employee.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <button class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 mr-4">
                                            View
                                        </button>
                                        <button class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="employee in employees.data"
                            :key="employee.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        {{ employee.first_name }} {{ employee.last_name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ employee.employee_id }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ employee.department?.name ?? 'N/A' }} &mdash; {{ employee.role?.name ?? 'N/A' }}
                                    </p>
                                </div>
                                <span
                                    :class="getStatusBadgeClass(employee.status)"
                                    class="ml-2 inline-flex flex-shrink-0 rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ employee.status.replace('_', ' ') }}
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <button class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center">
                                    View
                                </button>
                                <button class="flex-1 text-center rounded-md bg-secondary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-700 min-h-[44px] flex items-center justify-center">
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="employees.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="employees.prev_page_url"
                                :href="employees.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="employees.next_page_url"
                                :href="employees.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing
                                    <span class="font-medium">{{ employees.from }}</span>
                                    to
                                    <span class="font-medium">{{ employees.to }}</span>
                                    of
                                    <span class="font-medium">{{ employees.total }}</span>
                                    employees
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                    <Link
                                        v-for="link in employees.links"
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
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
