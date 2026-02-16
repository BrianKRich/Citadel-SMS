<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Object,
    filters: Object,
    courses: Array,
});

const showSuccess = ref(!!props.categories.flash?.success);
const showError = ref(!!props.categories.flash?.error);

const selectedCourse = ref(props.filters?.course_id || '');

function applyFilter() {
    router.get(route('admin.assessment-categories.index'), {
        course_id: selectedCourse.value || undefined,
    }, { preserveState: true, replace: true });
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.assessment-categories.destroy', id));
    }
}
</script>

<template>
    <Head title="Assessment Categories" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Assessment Categories
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
                            title="Assessment Categories"
                            :description="`${categories.total} categories configured`"
                        />

                        <div class="sm:ml-auto">
                            <Link :href="route('admin.assessment-categories.create')">
                                <PrimaryButton class="w-full sm:w-auto">
                                    + Add Category
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <select
                                v-model="selectedCourse"
                                @change="applyFilter"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Courses (including Global)</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">
                                    {{ course.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Course
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Weight
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ category.name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span v-if="category.course" class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ category.course.name }}
                                        </span>
                                        <span v-else class="inline-flex rounded-full bg-purple-100 dark:bg-purple-900 px-2 text-xs font-semibold leading-5 text-purple-800 dark:text-purple-300">
                                            Global
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ (category.weight * 100).toFixed(0) }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                            {{ category.description || '—' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.assessment-categories.edit', category.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 mr-4"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteCategory(category.id)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="categories.data.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No categories found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="category in categories.data"
                            :key="category.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ category.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Weight: {{ (category.weight * 100).toFixed(0) }}%
                                    </p>
                                    <p v-if="category.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ category.description }}
                                    </p>
                                </div>
                                <span v-if="category.course" class="ml-2 text-xs text-gray-600 dark:text-gray-400">
                                    {{ category.course.name }}
                                </span>
                                <span v-else class="ml-2 inline-flex rounded-full bg-purple-100 dark:bg-purple-900 px-2 py-1 text-xs font-semibold text-purple-800 dark:text-purple-300">
                                    Global
                                </span>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <Link
                                    :href="route('admin.assessment-categories.edit', category.id)"
                                    class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteCategory(category.id)"
                                    class="flex-1 text-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div v-if="categories.data.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No categories found.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="categories.links && categories.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="categories.prev_page_url"
                                :href="categories.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="categories.next_page_url"
                                :href="categories.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing
                                    <span class="font-medium">{{ categories.from }}</span>
                                    to
                                    <span class="font-medium">{{ categories.to }}</span>
                                    of
                                    <span class="font-medium">{{ categories.total }}</span>
                                    categories
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                    <Link
                                        v-for="link in categories.links"
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
