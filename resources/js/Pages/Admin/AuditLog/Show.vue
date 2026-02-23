<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    log: Object,
});

function getActionBadgeClass(action) {
    const map = {
        created: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        updated: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        deleted: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        restored: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    };
    return map[action] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString();
}

const diffRows = computed(() => {
    const old = props.log.old_values ?? {};
    const new_ = props.log.new_values ?? {};
    const keys = [...new Set([...Object.keys(old), ...Object.keys(new_)])];
    return keys.map(k => ({
        field: k,
        before: old[k] !== undefined ? String(old[k]) : null,
        after: new_[k] !== undefined ? String(new_[k]) : null,
    }));
});

const hasDiff = computed(() => diffRows.value.length > 0);
</script>

<template>
    <Head title="Audit Log Entry" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Audit Log Entry" description="Detail view of a recorded change" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Audit Log', href: route('admin.audit-log.index') },
                    { label: 'Entry' },
                ]" />

                <!-- Back link -->
                <div class="mb-6">
                    <Link :href="route('admin.audit-log.index')"
                        class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                        &larr; Back to Audit Log
                    </Link>
                </div>

                <!-- Summary card -->
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Event Summary</h3>
                    </div>
                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Action</dt>
                                <dd class="mt-1">
                                    <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', getActionBadgeClass(log.action)]">
                                        {{ log.action }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actor</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ log.user?.name ?? 'System' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Record</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ log.auditable_type_short }} — {{ log.subject_label }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">When</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(log.created_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Diff table -->
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Change Details</h3>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700">
                        <div v-if="!hasDiff" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No diff available for this event.
                        </div>
                        <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Before</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">After</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="row in diffRows" :key="row.field" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="whitespace-nowrap px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ row.field }}</td>
                                    <td class="px-6 py-3 text-sm text-red-700 dark:text-red-400">
                                        {{ row.before ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-green-700 dark:text-green-400">
                                        {{ row.after ?? '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
