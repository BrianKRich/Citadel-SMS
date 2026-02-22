<script setup>
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    users: Array,
    purgeLogs: Array,
});

const form = ref({
    model_type: props.filters?.model_type ?? '',
    user_id: props.filters?.user_id ?? '',
    action: props.filters?.action ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

const modelTypes = [
    { value: '', label: 'All Models' },
    { value: 'Student', label: 'Student' },
    { value: 'Employee', label: 'Employee' },
    { value: 'Grade', label: 'Grade' },
    { value: 'Enrollment', label: 'Enrollment' },
];

const actions = [
    { value: '', label: 'All Actions' },
    { value: 'created', label: 'Created' },
    { value: 'updated', label: 'Updated' },
    { value: 'deleted', label: 'Deleted' },
    { value: 'restored', label: 'Restored' },
];

function applyFilters() {
    const params = {};
    if (form.value.model_type) params.model_type = form.value.model_type;
    if (form.value.user_id) params.user_id = form.value.user_id;
    if (form.value.action) params.action = form.value.action;
    if (form.value.date_from) params.date_from = form.value.date_from;
    if (form.value.date_to) params.date_to = form.value.date_to;
    router.get(route('admin.audit-log.index'), params, { preserveState: true });
}

function clearFilters() {
    form.value = { model_type: '', user_id: '', action: '', date_from: '', date_to: '' };
    router.get(route('admin.audit-log.index'));
}

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

const purgeOptions = [
    { value: '30',  label: 'Older than 30 days' },
    { value: '90',  label: 'Older than 90 days' },
    { value: '180', label: 'Older than 180 days' },
    { value: '365', label: 'Older than 1 year' },
    { value: 'all', label: 'All records' },
];

const purgeForm = useForm({ older_than: '90', reason: '' });

function submitPurge() {
    if (!purgeForm.reason.trim()) {
        alert('A reason is required before purging.');
        return;
    }
    const option = purgeOptions.find(o => o.value === purgeForm.older_than);
    const label = option?.label?.toLowerCase() ?? purgeForm.older_than;
    if (!confirm(`Permanently delete audit log entries (${label})? This cannot be undone.`)) return;
    purgeForm.delete(route('admin.audit-log.purge'), {
        onSuccess: () => { purgeForm.reset('reason'); },
    });
}

function olderThanLabel(val) {
    return purgeOptions.find(o => o.value === val)?.label ?? val;
}
</script>

<template>
    <Head title="Audit Log" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Audit Log" description="Review who changed what and when" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Flash message -->
                <div v-if="$page.props.flash?.success"
                    class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900 dark:text-green-200">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Model</label>
                            <select v-model="form.model_type"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option v-for="m in modelTypes" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Actor</label>
                            <select v-model="form.user_id"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option value="">All Users</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Action</label>
                            <select v-model="form.action"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option v-for="a in actions" :key="a.value" :value="a.value">{{ a.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">From</label>
                            <input type="date" v-model="form.date_from"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">To</label>
                            <input type="date" v-model="form.date_to"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="applyFilters"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Apply Filters
                        </button>
                        <button @click="clearFilters"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Purge panel -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Purge Logs</h3>
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Delete entries</label>
                            <select v-model="purgeForm.older_than"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <option v-for="o in purgeOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-64">
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Reason <span class="text-red-500">*</span>
                            </label>
                            <input type="text" v-model="purgeForm.reason" maxlength="1000"
                                placeholder="Explain why these records are being purged…"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                :class="purgeForm.errors.reason ? 'border-red-500' : ''" />
                            <p v-if="purgeForm.errors.reason" class="mt-1 text-xs text-red-600">{{ purgeForm.errors.reason }}</p>
                        </div>
                        <button @click="submitPurge" :disabled="purgeForm.processing"
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20">
                            Purge
                        </button>
                    </div>
                </div>

                <!-- Purge history -->
                <div v-if="purgeLogs.length > 0" class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="px-6 py-3">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Purge History</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">When</th>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Performed By</th>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Scope</th>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Records Deleted</th>
                                <th class="px-6 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="pl in purgeLogs" :key="pl.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(pl.created_at) }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ pl.user_name }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ olderThanLabel(pl.older_than) }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ pl.purged_count.toLocaleString() }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ pl.reason }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Desktop table -->
                <div class="hidden overflow-hidden rounded-lg bg-white shadow md:block dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">When</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Actor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Model</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Subject</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No audit log entries found.</td>
                            </tr>
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ formatDate(log.created_at) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ log.user?.name ?? 'System' }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', getActionBadgeClass(log.action)]">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ log.auditable_type_short }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ log.subject_label }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('admin.audit-log.show', log.id)"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile cards -->
                <div class="space-y-4 md:hidden">
                    <div v-if="logs.data.length === 0" class="rounded-lg bg-white p-6 text-center text-sm text-gray-500 shadow dark:bg-gray-800 dark:text-gray-400">
                        No audit log entries found.
                    </div>
                    <div v-for="log in logs.data" :key="log.id"
                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', getActionBadgeClass(log.action)]">
                                    {{ log.action }}
                                </span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ log.auditable_type_short }}</span>
                            </div>
                            <Link :href="route('admin.audit-log.show', log.id)"
                                class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                View
                            </Link>
                        </div>
                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ log.subject_label }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ log.user?.name ?? 'System' }} &mdash; {{ formatDate(log.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="logs.last_page > 1" class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ logs.from }}–{{ logs.to }} of {{ logs.total }} entries
                    </p>
                    <div class="flex gap-1">
                        <Link v-for="link in logs.links" :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700',
                                !link.url ? 'cursor-not-allowed opacity-50' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
