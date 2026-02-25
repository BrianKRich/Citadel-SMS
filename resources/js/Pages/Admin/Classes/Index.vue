<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    classes:       { type: Object, required: true },
    academicYears: { type: Array, default: () => [] },
    filters:       { type: Object, default: () => ({}) },
    currentYearId: { type: Number, default: null },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const search           = ref(props.filters.search ?? '');
const academicYearId   = ref(props.filters.academic_year_id ?? props.currentYearId ?? '');
const status           = ref(props.filters.status ?? '');

let debounceTimer = null;

function applyFilters() {
    router.get(route('admin.classes.index'), {
        search:           search.value || undefined,
        academic_year_id: academicYearId.value || undefined,
        status:           status.value || undefined,
    }, { preserveState: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});

watch([academicYearId, status], applyFilters);

const getStatusBadgeClass = (s) => {
    const map = {
        forming:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        active:    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[s] || map.forming;
};

const formatDate = (d) => d ? d.substring(0, 10) : '—';
</script>

<template>
    <Head title="Class Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Class Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Class Management', href: route('admin.class-layout.index') },
                    { label: 'Class Setup' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <PageHeader
                            title="Classes"
                            :description="`${classes.total} class${classes.total === 1 ? '' : 'es'} total`"
                        />
                        <div class="sm:ml-auto">
                            <Link
                                :href="route('admin.classes.create')"
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >+ Add New Class</Link>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Class number, NGB number..."
                                :class="inputClass"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Academic Year</label>
                            <select v-model="academicYearId" :class="inputClass">
                                <option value="">All Years</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                    {{ year.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="status" :class="inputClass">
                                <option value="">All Statuses</option>
                                <option value="forming">Forming</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Class Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">NGB Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Academic Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-if="classes.data.length === 0">
                                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No classes found.
                                    </td>
                                </tr>
                                <tr
                                    v-for="cls in classes.data"
                                    :key="cls.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Class {{ cls.class_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.name || '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.ngb_number ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.academic_year?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="getStatusBadgeClass(cls.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >{{ cls.status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(cls.start_date) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(cls.end_date) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-4">
                                        <Link
                                            :href="route('admin.classes.show', cls.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >View</Link>
                                        <Link
                                            :href="route('admin.classes.edit', cls.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >Edit</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <div v-if="classes.data.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                            No classes found.
                        </div>
                        <div
                            v-for="cls in classes.data"
                            :key="cls.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        Class {{ cls.class_number }}
                                    </h3>
                                    <p v-if="cls.name" class="text-sm text-gray-700 dark:text-gray-300">{{ cls.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ cls.ngb_number ?? '—' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ cls.academic_year?.name ?? '—' }}</p>
                                </div>
                                <span :class="getStatusBadgeClass(cls.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize">
                                    {{ cls.status }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 space-y-1">
                                <div>Start: {{ formatDate(cls.start_date) }}</div>
                                <div>End: {{ formatDate(cls.end_date) }}</div>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('admin.classes.show', cls.id)"
                                    class="flex-1 text-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 min-h-[44px] flex items-center justify-center"
                                >View</Link>
                                <Link
                                    :href="route('admin.classes.edit', cls.id)"
                                    class="flex-1 text-center rounded-md bg-secondary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-700 min-h-[44px] flex items-center justify-center"
                                >Edit</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="classes.links && classes.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="classes.prev_page_url"
                                :href="classes.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Previous</Link>
                            <Link
                                v-if="classes.next_page_url"
                                :href="classes.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >Next</Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing <span class="font-medium">{{ classes.from }}</span> to <span class="font-medium">{{ classes.to }}</span> of <span class="font-medium">{{ classes.total }}</span> classes
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in classes.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-primary-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        !link.url ? 'pointer-events-none opacity-50' : '',
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
