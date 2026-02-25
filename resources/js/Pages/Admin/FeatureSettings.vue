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

const gradesForm = useForm({
    grades_enabled: page.props.features?.grades_enabled ?? false,
});

const reportCardsForm = useForm({
    report_cards_enabled: page.props.features?.report_cards_enabled ?? false,
});

const documentsForm = useForm({
    documents_enabled: page.props.features?.documents_enabled ?? false,
});

const staffTrainingForm = useForm({
    staff_training_enabled: page.props.features?.staff_training_enabled ?? false,
});

const academySetupForm = useForm({
    academy_setup_enabled: page.props.features?.academy_setup_enabled ?? false,
});

const customFieldsForm = useForm({
    custom_fields_enabled: page.props.features?.custom_fields_enabled ?? false,
});

const transcriptsForm = useForm({
    transcripts_enabled: page.props.features?.transcripts_enabled ?? false,
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

function toggleGrades() {
    gradesForm.grades_enabled = !gradesForm.grades_enabled;
    gradesForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleReportCards() {
    reportCardsForm.report_cards_enabled = !reportCardsForm.report_cards_enabled;
    reportCardsForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleDocuments() {
    documentsForm.documents_enabled = !documentsForm.documents_enabled;
    documentsForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleStaffTraining() {
    staffTrainingForm.staff_training_enabled = !staffTrainingForm.staff_training_enabled;
    staffTrainingForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleAcademySetup() {
    academySetupForm.academy_setup_enabled = !academySetupForm.academy_setup_enabled;
    academySetupForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleCustomFields() {
    customFieldsForm.custom_fields_enabled = !customFieldsForm.custom_fields_enabled;
    customFieldsForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
}

function toggleTranscripts() {
    transcriptsForm.transcripts_enabled = !transcriptsForm.transcripts_enabled;
    transcriptsForm.post(route('admin.feature-settings.update'), { preserveScroll: true });
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
    {
        key: 'grades',
        label: 'Grade Management',
        description: 'Show the Grade Management card in Quick Actions. When disabled, grades and assessments are still accessible through class pages.',
        enabled: () => page.props.features?.grades_enabled,
        processing: () => gradesForm.processing,
        toggle: toggleGrades,
    },
    {
        key: 'report_cards',
        label: 'Report Cards',
        description: 'Show the Report Cards card in Quick Actions. When disabled, report cards are still accessible from individual student records.',
        enabled: () => page.props.features?.report_cards_enabled,
        processing: () => reportCardsForm.processing,
        toggle: toggleReportCards,
    },
    {
        key: 'documents',
        label: 'Document Management',
        description: 'Enable document uploads for students, employees, and institution-wide files. All documents are stored privately and require authentication to download.',
        enabled: () => page.props.features?.documents_enabled,
        processing: () => documentsForm.processing,
        toggle: toggleDocuments,
    },
    {
        key: 'staff_training',
        label: 'Staff Training',
        description: 'Track annual staff training courses and completions. Log who completed which courses, when, and who trained them. Supports certificate uploads.',
        enabled: () => page.props.features?.staff_training_enabled,
        processing: () => staffTrainingForm.processing,
        toggle: toggleStaffTraining,
    },
    {
        key: 'academy_setup',
        label: 'Academy Setup',
        description: 'Academy name, address, contact info, and structure configuration. Manage departments and employee roles.',
        enabled: () => page.props.features?.academy_setup_enabled,
        processing: () => academySetupForm.processing,
        toggle: toggleAcademySetup,
    },
    {
        key: 'custom_fields',
        label: 'Custom Fields',
        description: 'Define additional fields for students, employees, courses, classes, and enrollments.',
        enabled: () => page.props.features?.custom_fields_enabled,
        processing: () => customFieldsForm.processing,
        toggle: toggleCustomFields,
    },
    {
        key: 'transcripts',
        label: 'Transcripts',
        description: 'Enable the Transcripts Quick Action card and transcript generation for students.',
        enabled: () => page.props.features?.transcripts_enabled,
        processing: () => transcriptsForm.processing,
        toggle: toggleTranscripts,
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
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Flash -->
                <div v-if="$page.props.flash?.success" class="mb-6">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>

                <PageHeader
                    title="Feature Settings"
                    description="Enable or disable application features. Changes take effect immediately for all users."
                />

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="flex items-start justify-between rounded-lg bg-white p-5 shadow dark:bg-gray-800"
                    >
                        <div class="flex-1 pr-6">
                            <div class="flex items-center gap-2">
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
                                'relative mt-0.5 inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50'
                            ]"
                        >
                            <span
                                :class="[
                                    feature.enabled() ? 'translate-x-5' : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                                ]"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
