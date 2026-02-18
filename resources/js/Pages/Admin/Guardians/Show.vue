<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    guardian: Object,
});

function deleteGuardian() {
    if (confirm('Are you sure you want to delete this guardian? This action cannot be undone.')) {
        router.delete(route('admin.guardians.destroy', props.guardian.id));
    }
}
</script>

<template>
    <Head :title="`${guardian.first_name} ${guardian.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Guardian Detail</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Top actions -->
                <div class="mb-6 flex items-center justify-between">
                    <Link
                        :href="route('admin.guardians.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Guardians
                    </Link>
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.guardians.edit', guardian.id)">
                            <PrimaryButton>Edit</PrimaryButton>
                        </Link>
                        <button
                            @click="deleteGuardian"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Card 1: Guardian Info -->
                <Card class="mb-6">
                    <div class="mb-4 flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ guardian.first_name }} {{ guardian.last_name }}
                        </h3>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                            {{ guardian.relationship.charAt(0).toUpperCase() + guardian.relationship.slice(1) }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ guardian.email ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ guardian.phoneNumbers?.[0]
                                    ? '(' + guardian.phoneNumbers[0].area_code + ') ' + guardian.phoneNumbers[0].number
                                    : '—' }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">
                                {{ guardian.address ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Occupation</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ guardian.occupation ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Linked Students -->
                <Card>
                    <PageHeader title="Linked Students" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Primary</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="student in guardian.students" :key="student.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ student.student_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ student.first_name }} {{ student.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            v-if="student.pivot?.is_primary"
                                            class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >
                                            Primary
                                        </span>
                                        <span v-else class="text-gray-400 dark:text-gray-600">—</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.students.show', student.id)"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!guardian.students?.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No students linked to this guardian.
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
