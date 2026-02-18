<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import AdminActionCard from '@/Components/Admin/AdminActionCard.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});

const page = usePage();

const featureForm = useForm({
    attendance_enabled: page.props.features?.attendance_enabled ?? false,
});

function toggleAttendance() {
    featureForm.attendance_enabled = !featureForm.attendance_enabled;
    featureForm.post(route('admin.feature-settings.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>

                <!-- Welcome Section -->
                <div class="mb-8">
                    <PageHeader
                        title="Welcome to Admin"
                        description="Manage your Student Management System application from this central dashboard."
                    />
                </div>

                <!-- Statistics Grid -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">User Statistics</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <StatCard
                            title="Total Users"
                            :value="stats.total_users"
                            icon="👥"
                        />

                        <StatCard
                            title="New Today"
                            :value="stats.users_today"
                            icon="📅"
                            trend="up"
                            :trend-value="stats.users_today > 0 ? '+' + stats.users_today : '0'"
                        />

                        <StatCard
                            title="This Week"
                            :value="stats.users_this_week"
                            icon="📊"
                        />

                        <StatCard
                            title="This Month"
                            :value="stats.users_this_month"
                            icon="📈"
                        />
                    </div>
                </div>

                <!-- Grade Statistics -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Grade Statistics</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <StatCard
                            title="Total Assessments"
                            :value="stats.total_assessments"
                            icon="📝"
                        />

                        <StatCard
                            title="Grades This Week"
                            :value="stats.grades_this_week"
                            icon="✅"
                        />

                        <StatCard
                            title="Average GPA"
                            :value="stats.average_gpa || '—'"
                            icon="🎓"
                        />
                    </div>
                </div>

                <!-- Admin Actions -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <AdminActionCard
                            title="Theme Settings"
                            description="Customize colors and appearance of your application"
                            icon="🎨"
                            :href="route('admin.theme')"
                            color="accent"
                        />

                        <AdminActionCard
                            title="Student Management"
                            description="Manage student records and enrollment"
                            icon="📚"
                            :href="route('admin.students.index')"
                            color="primary"
                        />

                        <AdminActionCard
                            title="User Management"
                            description="View and manage registered users"
                            icon="👤"
                            :href="route('admin.users.index')"
                            color="secondary"
                        />

                        <AdminActionCard
                            title="Class Management"
                            description="Manage class schedules and enrollment"
                            icon="📖"
                            :href="route('admin.classes.index')"
                            color="primary"
                        />

                        <AdminActionCard
                            title="Employee Management"
                            description="Manage employee profiles and assignments"
                            icon="👨‍🏫"
                            :href="route('admin.employees.index')"
                            color="secondary"
                        />

                        <AdminActionCard
                            title="Course Catalog"
                            description="Manage course offerings and curriculum"
                            icon="📚"
                            :href="route('admin.courses.index')"
                            color="accent"
                        />

                        <AdminActionCard
                            title="Academic Years"
                            description="Manage academic years and terms"
                            icon="📅"
                            :href="route('admin.academic-years.index')"
                            color="primary"
                        />

                        <AdminActionCard
                            title="Grade Management"
                            description="Enter grades, manage assessments, and view student performance"
                            icon="📊"
                            :href="route('admin.grades.index')"
                            color="accent"
                        />

                        <AdminActionCard
                            title="Report Cards"
                            description="View and download per-term report cards for students"
                            icon="📄"
                            :href="route('admin.report-cards.index')"
                            color="primary"
                        />

                        <AdminActionCard
                            title="Transcripts"
                            description="Generate official and unofficial student transcripts"
                            icon="📜"
                            :href="route('admin.transcripts.index')"
                            color="secondary"
                        />

                        <AdminActionCard
                            v-if="$page.props.features?.attendance_enabled"
                            title="Attendance"
                            description="Track and manage student attendance by class"
                            icon="✅"
                            :href="route('admin.attendance.index')"
                            color="primary"
                        />

                    </div>
                </div>

                <!-- Feature Settings -->
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Feature Settings</h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Attendance Tracking</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Enable attendance management for all classes.
                                    {{ $page.props.features?.attendance_enabled ? 'Currently enabled.' : 'Currently disabled.' }}
                                </p>
                            </div>
                            <button
                                @click="toggleAttendance"
                                :disabled="featureForm.processing"
                                :class="[
                                    $page.props.features?.attendance_enabled
                                        ? 'bg-primary-600 hover:bg-primary-700'
                                        : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600',
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50'
                                ]"
                            >
                                <span
                                    :class="[
                                        $page.props.features?.attendance_enabled ? 'translate-x-5' : 'translate-x-0',
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                                    ]"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity (Placeholder) -->
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-sm">Activity feed will appear here</p>
                            <p class="mt-1 text-xs">Track user registrations, student enrollment, and other events</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
