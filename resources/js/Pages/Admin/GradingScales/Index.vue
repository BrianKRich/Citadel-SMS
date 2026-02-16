<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    scales: Object,
});

const showSuccess = ref(!!props.scales.flash?.success);
const showError = ref(!!props.scales.flash?.error);

function setDefault(id) {
    router.post(route('admin.grading-scales.set-default', id));
}

function deleteScale(id) {
    if (confirm('Are you sure you want to delete this grading scale?')) {
        router.delete(route('admin.grading-scales.destroy', id));
    }
}
</script>

<template>
    <Head title="Grading Scales" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Grading Scales
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Alerts -->
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert
                        type="success"
                        :message="$page.props.flash.success"
                        @dismiss="showSuccess = false"
                    />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert
                        type="error"
                        :message="$page.props.flash.error"
                        @dismiss="showError = false"
                    />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Grading Scales"
                            :description="`${scales.total} grading scales configured`"
                        />
                        <div class="sm:ml-auto">
                            <Link :href="route('admin.grading-scales.create')">
                                <PrimaryButton class="w-full sm:w-auto">
                                    + Add Grading Scale
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Default</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Entries</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="scale in scales.data" :key="scale.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ scale.name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            v-if="scale.is_default"
                                            class="inline-flex rounded-full bg-green-100 dark:bg-green-900 px-2 text-xs font-semibold leading-5 text-green-800 dark:text-green-300"
                                        >
                                            Default
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ scale.entries_count ?? scale.entries?.length ?? 0 }} entries
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <button
                                            v-if="!scale.is_default"
                                            @click="setDefault(scale.id)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-4"
                                        >
                                            Set Default
                                        </button>
                                        <Link
                                            :href="route('admin.grading-scales.edit', scale.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 mr-4"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteScale(scale.id)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="scales.data.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No grading scales found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="scale in scales.data"
                            :key="scale.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ scale.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ scale.entries_count ?? scale.entries?.length ?? 0 }} entries
                                    </p>
                                </div>
                                <span
                                    v-if="scale.is_default"
                                    class="ml-2 inline-flex rounded-full bg-green-100 dark:bg-green-900 px-2 py-1 text-xs font-semibold text-green-800 dark:text-green-300"
                                >
                                    Default
                                </span>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button
                                    v-if="!scale.is_default"
                                    @click="setDefault(scale.id)"
                                    class="flex-1 text-center rounded-md border border-blue-300 dark:border-blue-600 px-3 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 min-h-[44px] flex items-center justify-center"
                                >
                                    Set Default
                                </button>
                                <Link
                                    :href="route('admin.grading-scales.edit', scale.id)"
                                    class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteScale(scale.id)"
                                    class="flex-1 text-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div v-if="scales.data.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No grading scales found.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="scales.links && scales.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="scales.prev_page_url"
                                :href="scales.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="scales.next_page_url"
                                :href="scales.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ scales.from }}</span>
                                to
                                <span class="font-medium">{{ scales.to }}</span>
                                of
                                <span class="font-medium">{{ scales.total }}</span>
                                scales
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in scales.links"
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
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
