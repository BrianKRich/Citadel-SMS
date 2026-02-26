<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    assessments: Object,
    filters: Object,
    classes: Array,
    categories: Array,
});

const showSuccess = ref(!!props.assessments.flash?.success);
const showError = ref(!!props.assessments.flash?.error);

const filterClass = ref(props.filters?.class_id || '');
const filterCategory = ref(props.filters?.category_id || '');
const filterStatus = ref(props.filters?.status || '');

function applyFilters() {
    router.get(route('admin.assessments.index'), {
        class_id: filterClass.value || undefined,
        category_id: filterCategory.value || undefined,
        status: filterStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

function getStatusBadgeClass(status) {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return classes[status] || classes.draft;
}

function deleteAssessment(id) {
    if (confirm('Are you sure you want to delete this assessment?')) {
        router.delete(route('admin.assessments.destroy', id));
    }
}
</script>

<template>
    <Head title="Assessments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Assessments
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
                            title="Assessments"
                            :description="`${assessments.total} assessments total`"
                        />
                        <div class="sm:ml-auto">
                            <Link :href="route('admin.assessments.create')">
                                <PrimaryButton class="w-full sm:w-auto">
                                    + Add Assessment
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <select
                            v-model="filterClass"
                            @change="applyFilters"
                            class="block w-full sm:w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        >
                            <option value="">All Classes</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                {{ cls.course?.name || 'N/A' }} — {{ cls.section_name }}
                            </option>
                        </select>
                        <select
                            v-model="filterCategory"
                            @change="applyFilters"
                            class="block w-full sm:w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <select
                            v-model="filterStatus"
                            @change="applyFilters"
                            class="block w-full sm:w-36 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Max Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="assessment in assessments.data" :key="assessment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ assessment.name }}
                                        </div>
                                        <div v-if="assessment.is_extra_credit" class="text-xs text-yellow-600 dark:text-yellow-400">
                                            Extra Credit
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ assessment.class_course?.course?.name || 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ assessment.class_course?.class?.section_name || '' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ assessment.assessment_category?.name || '—' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ assessment.max_score }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ assessment.due_date ? new Date(assessment.due_date).toLocaleDateString() : '—' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(assessment.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{ assessment.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.assessments.show', assessment.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 mr-3"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('admin.grades.enter', assessment.id)"
                                            class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 mr-3"
                                        >
                                            Grade
                                        </Link>
                                        <Link
                                            :href="route('admin.assessments.edit', assessment.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 mr-3"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteAssessment(assessment.id)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="assessments.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No assessments found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="assessment in assessments.data"
                            :key="assessment.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ assessment.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ assessment.class_course?.course?.name || 'N/A' }} — {{ assessment.class_course?.class?.section_name || '' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ assessment.assessment_category?.name || 'No category' }} • Max: {{ assessment.max_score }}
                                        <span v-if="assessment.due_date"> • Due: {{ new Date(assessment.due_date).toLocaleDateString() }}</span>
                                    </p>
                                </div>
                                <span
                                    :class="getStatusBadgeClass(assessment.status)"
                                    class="ml-2 inline-flex flex-shrink-0 rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ assessment.status }}
                                </span>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <Link
                                    :href="route('admin.assessments.show', assessment.id)"
                                    class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="route('admin.grades.enter', assessment.id)"
                                    class="flex-1 text-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Grade
                                </Link>
                                <Link
                                    :href="route('admin.assessments.edit', assessment.id)"
                                    class="flex-1 text-center rounded-md bg-secondary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-700 min-h-[44px] flex items-center justify-center"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                        <div v-if="assessments.data.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No assessments found.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="assessments.links && assessments.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="assessments.prev_page_url"
                                :href="assessments.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="assessments.next_page_url"
                                :href="assessments.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ assessments.from }}</span>
                                to
                                <span class="font-medium">{{ assessments.to }}</span>
                                of
                                <span class="font-medium">{{ assessments.total }}</span>
                                assessments
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in assessments.links"
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
