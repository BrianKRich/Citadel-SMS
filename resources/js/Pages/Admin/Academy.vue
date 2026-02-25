<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    academy: { type: Object, default: () => ({}) },
});

const hasData = computed(() =>
    !!(props.academy.name || props.academy.address || props.academy.phone ||
       props.academy.director || props.academy.year_started)
);

const editing = ref(!hasData.value);

const form = useForm({
    name:         props.academy.name ?? '',
    address:      props.academy.address ?? '',
    phone:        props.academy.phone ?? '',
    director:     props.academy.director ?? '',
    year_started: props.academy.year_started ?? '',
});

function submit() {
    form.post(route('admin.academy.update'), {
        preserveScroll: true,
        onSuccess: () => { editing.value = false; },
    });
}

function cancelEdit() {
    form.reset();
    form.name         = props.academy.name ?? '';
    form.address      = props.academy.address ?? '';
    form.phone        = props.academy.phone ?? '';
    form.director     = props.academy.director ?? '';
    form.year_started = props.academy.year_started ?? '';
    editing.value = false;
}

const quickActions = [
    {
        title: 'Department Management',
        description: 'Manage departments within the academy.',
        icon: '🏛️',
        href: route('admin.departments.index'),
        color: 'primary',
        disabled: false,
    },
    {
        title: 'Employee Roles',
        description: 'Define roles for employees within each department.',
        icon: '👔',
        href: route('admin.employee-roles.index'),
        color: 'secondary',
        disabled: false,
    },
    {
        title: 'Permissions',
        description: 'Configure data access controls and permissions for roles in each department.',
        icon: '🔐',
        href: '#',
        color: 'accent',
        disabled: true,
    },
    {
        title: 'Grading Policies',
        description: 'Set academy-wide grading policies and GPA rules.',
        icon: '📋',
        href: '#',
        color: 'primary',
        disabled: true,
    },
    {
        title: 'Calendar & Schedules',
        description: 'Manage academic calendar events and bell schedules.',
        icon: '📅',
        href: '#',
        color: 'secondary',
        disabled: true,
    },
    {
        title: 'Notifications',
        description: 'Configure automated notifications and alerts.',
        icon: '🔔',
        href: '#',
        color: 'accent',
        disabled: true,
    },
];
</script>

<template>
    <Head title="Academy Setup" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Academy Setup
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academy Setup' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Academy Information -->
                <Card>
                    <div class="flex items-start justify-between gap-4">
                        <PageHeader
                            title="Academy Information"
                            description="Basic information about your academy displayed on reports and documents."
                        />
                        <button
                            v-if="hasData && !editing"
                            @click="editing = true"
                            class="shrink-0 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600"
                        >Edit</button>
                    </div>

                    <!-- Read-only view -->
                    <dl v-if="hasData && !editing" class="mt-6 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Academy Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ academy.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Director Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ academy.director || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ academy.phone || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Year Founded</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ academy.year_started || '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ academy.address || '—' }}</dd>
                        </div>
                    </dl>

                    <!-- Edit form -->
                    <form v-else @submit.prevent="submit" class="mt-6 space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Academy Name
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    maxlength="255"
                                    placeholder="e.g. Georgia Job Challenge Academy"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Director Name
                                </label>
                                <input
                                    v-model="form.director"
                                    type="text"
                                    maxlength="255"
                                    placeholder="e.g. Jane Smith"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.director" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.director }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Phone Number
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    maxlength="50"
                                    placeholder="e.g. (555) 123-4567"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Year Founded
                                </label>
                                <input
                                    v-model="form.year_started"
                                    type="number"
                                    min="1900"
                                    :max="new Date().getFullYear() + 10"
                                    placeholder="e.g. 2010"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.year_started" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.year_started }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Address
                            </label>
                            <textarea
                                v-model="form.address"
                                rows="3"
                                maxlength="500"
                                placeholder="e.g. 123 Main Street, Atlanta, GA 30301"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            ></textarea>
                            <p v-if="form.errors.address" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                v-if="hasData"
                                type="button"
                                @click="cancelEdit"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving…' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </Card>

                <!-- Quick Actions -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Configuration</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <template v-for="action in quickActions" :key="action.title">
                            <!-- Active card -->
                            <Link
                                v-if="!action.disabled"
                                :href="action.href"
                                class="group relative overflow-hidden rounded-lg bg-white p-6 shadow transition hover:shadow-lg dark:bg-gray-800"
                            >
                                <div class="flex items-start space-x-4">
                                    <div :class="[
                                        'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg text-2xl',
                                        action.color === 'primary' ? 'bg-primary-100 dark:bg-primary-900' : '',
                                        action.color === 'secondary' ? 'bg-secondary-100 dark:bg-secondary-900' : '',
                                        action.color === 'accent' ? 'bg-accent-100 dark:bg-accent-900' : '',
                                    ]">
                                        {{ action.icon }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 :class="[
                                            'text-lg font-semibold text-gray-900 dark:text-gray-100',
                                            action.color === 'primary' ? 'group-hover:text-primary-600 dark:group-hover:text-primary-400' : '',
                                            action.color === 'secondary' ? 'group-hover:text-secondary-600 dark:group-hover:text-secondary-400' : '',
                                            action.color === 'accent' ? 'group-hover:text-accent-600 dark:group-hover:text-accent-400' : '',
                                        ]">
                                            {{ action.title }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ action.description }}</p>
                                    </div>
                                    <div :class="[
                                        'flex-shrink-0 text-gray-400 dark:text-gray-500',
                                        action.color === 'primary' ? 'group-hover:text-primary-600 dark:group-hover:text-primary-400' : '',
                                        action.color === 'secondary' ? 'group-hover:text-secondary-600 dark:group-hover:text-secondary-400' : '',
                                        action.color === 'accent' ? 'group-hover:text-accent-600 dark:group-hover:text-accent-400' : '',
                                    ]">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </Link>

                            <!-- Disabled "Coming Soon" card -->
                            <div
                                v-else
                                class="relative overflow-hidden rounded-lg bg-white p-6 shadow opacity-60 dark:bg-gray-800 cursor-not-allowed"
                            >
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Coming Soon
                                    </span>
                                </div>
                                <div class="flex items-start space-x-4">
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg text-2xl bg-gray-100 dark:bg-gray-700">
                                        {{ action.icon }}
                                    </div>
                                    <div class="flex-1 min-w-0 pr-16">
                                        <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">
                                            {{ action.title }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">{{ action.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
