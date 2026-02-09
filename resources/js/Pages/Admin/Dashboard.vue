<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import AdminActionCard from '@/Components/Admin/AdminActionCard.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});
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
                <!-- Welcome Section -->
                <div class="mb-8">
                    <PageHeader
                        title="Welcome to Admin"
                        description="Manage your Citadel SMS application from this central dashboard."
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

                <!-- Admin Actions -->
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <AdminActionCard
                            title="Theme Settings"
                            description="Customize colors and appearance of your application"
                            icon="🎨"
                            :href="route('admin.theme')"
                            color="purple"
                        />

                        <AdminActionCard
                            title="Student Management"
                            description="Manage student records and enrollment"
                            icon="📚"
                            href="#"
                            color="blue"
                        />

                        <AdminActionCard
                            title="User Management"
                            description="View and manage registered users"
                            icon="👤"
                            :href="route('admin.users.index')"
                            color="green"
                        />

                        <AdminActionCard
                            title="Course Management"
                            description="Manage courses and class schedules"
                            icon="📖"
                            href="#"
                            color="indigo"
                        />

                        <AdminActionCard
                            title="Analytics"
                            description="View reports and analytics data"
                            icon="📊"
                            href="#"
                            color="pink"
                        />

                        <AdminActionCard
                            title="Settings"
                            description="Configure application settings and preferences"
                            icon="⚙️"
                            href="#"
                            color="indigo"
                        />
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
