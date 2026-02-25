<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    academic_years: Object,
});

function setCurrent(yearId) {
    router.post(route('admin.academic-years.set-current', yearId));
}

const getStatusBadgeClass = (status) => {
    const map = {
        current:   'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        forming:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[status] || map.forming;
};
</script>

<template>
    <Head title="Academic Years" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Academic Years
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Class Management', href: route('admin.class-layout.index') },
                    { label: 'Academic Years' },
                ]" class="mb-4" />

                <!-- Alerts -->
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Academic Years"
                            :description="`Manage all ${academic_years.total} academic years`"
                        />

                        <div class="sm:ml-auto">
                            <Link
                                :href="route('admin.academic-years.create')"
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >+ Add New Academic Year</Link>
                        </div>
                    </div>

                    <!-- Academic Years List -->
                    <div class="space-y-6">
                        <div
                            v-for="year in academic_years.data"
                            :key="year.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ year.name }}
                                        </h3>
                                        <span
                                            :class="getStatusBadgeClass(year.status)"
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        >
                                            {{ year.status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ new Date(year.start_date).toLocaleDateString() }} - {{ new Date(year.end_date).toLocaleDateString() }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('admin.academic-years.edit', year.id)"
                                        class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 text-sm font-medium"
                                    >Edit</Link>
                                    <button
                                        v-if="year.status !== 'current'"
                                        @click="setCurrent(year.id)"
                                        class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 text-sm font-medium"
                                    >Set Current</button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <Link
                                    :href="route('admin.academic-years.show', year.id)"
                                    class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    View Classes &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="academic_years.links.length > 3" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="academic_years.prev_page_url"
                                :href="academic_years.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="academic_years.next_page_url"
                                :href="academic_years.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing
                                    <span class="font-medium">{{ academic_years.from }}</span>
                                    to
                                    <span class="font-medium">{{ academic_years.to }}</span>
                                    of
                                    <span class="font-medium">{{ academic_years.total }}</span>
                                    academic years
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                    <Link
                                        v-for="link in academic_years.links"
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
