<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    guardians: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
let searchTimeout = null;

watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.guardians.index'), { search: val }, { preserveState: true, replace: true });
    }, 300);
});
</script>

<template>
    <Head title="Guardians" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Guardians</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert />

                <Card>
                    <PageHeader title="Guardians">
                        <template #actions>
                            <Link :href="route('admin.guardians.create')">
                                <PrimaryButton>Add Guardian</PrimaryButton>
                            </Link>
                        </template>
                    </PageHeader>

                    <!-- Search -->
                    <div class="mb-6">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, email..."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Relationship</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="guardian in guardians.data" :key="guardian.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ guardian.first_name }} {{ guardian.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ guardian.relationship.charAt(0).toUpperCase() + guardian.relationship.slice(1) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ guardian.email ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ guardian.phoneNumbers[0] ? '(' + guardian.phoneNumbers[0].area_code + ') ' + guardian.phoneNumbers[0].number : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ guardian.students.length }} student(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <Link
                                            :href="route('admin.guardians.show', guardian.id)"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('admin.guardians.edit', guardian.id)"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!guardians.data.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No guardians found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="block sm:hidden space-y-4">
                        <div
                            v-for="guardian in guardians.data"
                            :key="guardian.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ guardian.first_name }} {{ guardian.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ guardian.relationship.charAt(0).toUpperCase() + guardian.relationship.slice(1) }}
                                    </p>
                                </div>
                                <div class="flex gap-3 text-sm">
                                    <Link
                                        :href="route('admin.guardians.show', guardian.id)"
                                        class="text-primary-600 hover:text-primary-800 dark:text-primary-400"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('admin.guardians.edit', guardian.id)"
                                        class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                <p v-if="guardian.email">{{ guardian.email }}</p>
                                <p>{{ guardian.phoneNumbers[0] ? '(' + guardian.phoneNumbers[0].area_code + ') ' + guardian.phoneNumbers[0].number : 'No phone' }}</p>
                                <p>{{ guardian.students.length }} student(s)</p>
                            </div>
                        </div>
                        <p v-if="!guardians.data.length" class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                            No guardians found.
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="guardians.links?.length > 3" class="mt-6 flex items-center justify-between">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ guardians.from }}–{{ guardians.to }} of {{ guardians.total }}
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in guardians.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 text-sm rounded-md"
                                    :class="link.active ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    v-html="link.label"
                                />
                                <span v-else class="px-3 py-1 text-sm text-gray-400 dark:text-gray-600" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
