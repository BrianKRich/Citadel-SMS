<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const attendanceForm = useForm({
    attendance_enabled: page.props.features?.attendance_enabled ?? false,
});

const themeForm = useForm({
    theme_enabled: page.props.features?.theme_enabled ?? false,
});

const recentActivityForm = useForm({
    recent_activity_enabled: page.props.features?.recent_activity_enabled ?? false,
});

function toggleAttendance() {
    attendanceForm.attendance_enabled = !attendanceForm.attendance_enabled;
    attendanceForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleTheme() {
    themeForm.theme_enabled = !themeForm.theme_enabled;
    themeForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleRecentActivity() {
    recentActivityForm.recent_activity_enabled = !recentActivityForm.recent_activity_enabled;
    recentActivityForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

const features = [
    {
        key: 'attendance',
        label: 'Attendance Tracking',
        description: 'Enable attendance management for all classes. When enabled, teachers can record daily attendance and generate attendance reports.',
        enabled: () => page.props.features?.attendance_enabled,
        processing: () => attendanceForm.processing,
        toggle: toggleAttendance,
    },
    {
        key: 'theme',
        label: 'Theme Settings',
        description: 'Allow administrators to customize the application\'s colors and appearance. When disabled, the current theme is preserved but cannot be changed.',
        enabled: () => page.props.features?.theme_enabled,
        processing: () => themeForm.processing,
        toggle: toggleTheme,
    },
    {
        key: 'recent_activity',
        label: 'Recent Activity Panel',
        description: 'Show the Recent Activity section on the admin dashboard. Disable to keep the dashboard more compact.',
        enabled: () => page.props.features?.recent_activity_enabled,
        processing: () => recentActivityForm.processing,
        toggle: toggleRecentActivity,
    },
];
</script>

<template>
    <Head title="Feature Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Feature Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="mb-6">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>

                <PageHeader
                    title="Feature Settings"
                    description="Enable or disable application features. Changes take effect immediately for all users."
                />

                <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li
                            v-for="feature in features"
                            :key="feature.key"
                            class="flex items-center justify-between px-6 py-5"
                        >
                            <div class="flex-1 pr-8">
                                <div class="flex items-center gap-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ feature.label }}
                                    </p>
                                    <span
                                        :class="[
                                            feature.enabled()
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium'
                                        ]"
                                    >
                                        {{ feature.enabled() ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ feature.description }}
                                </p>
                            </div>
                            <button
                                @click="feature.toggle()"
                                :disabled="feature.processing()"
                                :class="[
                                    feature.enabled()
                                        ? 'bg-primary-600 hover:bg-primary-700'
                                        : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600',
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50'
                                ]"
                            >
                                <span
                                    :class="[
                                        feature.enabled() ? 'translate-x-5' : 'translate-x-0',
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                                    ]"
                                />
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
