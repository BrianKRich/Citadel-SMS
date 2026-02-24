<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    institutions: { type: Object, required: true },
    filters:      { type: Object, default: () => ({}) },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const search = ref(props.filters.search ?? '');
let debounceTimer = null;

function applyFilters() {
    router.get(route('admin.institutions.index'), {
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
});

const typeLabel = (type) => {
    if (type === 'technical_college') return 'Technical College';
    if (type === 'university') return 'University';
    return type ?? '—';
};

function destroy(institution) {
    if (confirm(`Delete "${institution.name}"? This cannot be undone.`)) {
        router.delete(route('admin.institutions.destroy', institution.id));
    }
}
</script>

<template>
    <Head title="Institutions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Institutions</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Institutions' },
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
                            title="Institutions"
                            :description="`${institutions.total} institution${institutions.total === 1 ? '' : 's'} total`"
                        />
                        <div class="sm:ml-auto">
                            <Link
                                :href="route('admin.institutions.create')"
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >+ Add Institution</Link>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, contact..."
                            class="mt-1 block w-full sm:max-w-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Person</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-if="institutions.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No institutions found.</td>
                                </tr>
                                <tr
                                    v-for="inst in institutions.data"
                                    :key="inst.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ inst.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ typeLabel(inst.type) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ inst.contact_person ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ inst.phone ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-4">
                                        <Link
                                            :href="route('admin.institutions.edit', inst.id)"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300"
                                        >Edit</Link>
                                        <button
                                            type="button"
                                            @click="destroy(inst)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                        >Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        <div v-if="institutions.data.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">No institutions found.</div>
                        <div
                            v-for="inst in institutions.data"
                            :key="inst.id"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm"
                        >
                            <div class="mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ inst.name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ typeLabel(inst.type) }}</p>
                                <p v-if="inst.contact_person" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Contact: {{ inst.contact_person }}</p>
                                <p v-if="inst.phone" class="text-xs text-gray-500 dark:text-gray-400">Phone: {{ inst.phone }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('admin.institutions.edit', inst.id)"
                                    class="flex-1 text-center rounded-md bg-secondary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-secondary-700 min-h-[44px] flex items-center justify-center"
                                >Edit</Link>
                                <button
                                    type="button"
                                    @click="destroy(inst)"
                                    class="flex-1 text-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 min-h-[44px] flex items-center justify-center"
                                >Delete</button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="institutions.links && institutions.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link v-if="institutions.prev_page_url" :href="institutions.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50">Previous</Link>
                            <Link v-if="institutions.next_page_url" :href="institutions.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50">Next</Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing <span class="font-medium">{{ institutions.from }}</span> to <span class="font-medium">{{ institutions.to }}</span> of <span class="font-medium">{{ institutions.total }}</span> institutions
                            </p>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in institutions.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    :class="[
                                        link.active ? 'z-10 bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
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
