<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminActionCard from '@/Components/Admin/AdminActionCard.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head } from '@inertiajs/vue3';
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
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Class Management' },
                ]" />

                <PageHeader
                    title="Class Management"
                    description="Manage the academic structure — years, classes, and course catalog."
                />

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <AdminActionCard
                        title="Academic Years"
                        description="Manage academic years and classes"
                        icon="📅"
                        :href="route('admin.academic-years.index')"
                        color="primary"
                    />

                    <AdminActionCard
                        title="Class Setup"
                        description="Manage class schedules and enrollment"
                        icon="📖"
                        :href="route('admin.classes.index')"
                        color="secondary"
                    />

                    <AdminActionCard
                        title="Curriculum Management"
                        description="Manage course offerings and curriculum"
                        icon="📚"
                        :href="route('admin.courses.index')"
                        color="accent"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
