<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import UserForm from '@/Components/Users/UserForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    role: 'user',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create User
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Card>
                    <div class="mb-6">
                        <PageHeader
                            title="Add New User"
                            description="Create a new user account with role and permissions"
                        />
                    </div>

                    <UserForm :form="form" @submit="submit" />

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <Link
                            :href="route('admin.users.index')"
                            class="text-sm text-gray-600 hover:text-gray-900"
                        >
                            ← Back to Users
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
