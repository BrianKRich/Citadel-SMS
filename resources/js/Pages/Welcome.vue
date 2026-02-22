<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { useDarkMode } from '@/composables/useDarkMode';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Initialize theme and dark mode
useTheme();
useDarkMode();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Welcome to Student Management System" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-sm shadow-sm dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <!-- Left: App Title -->
                    <div class="flex items-center space-x-3">
                        <ApplicationLogo class="h-10 w-10 fill-current text-primary-600 dark:text-primary-400" />
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">
                            Student Management System
                        </h1>
                    </div>

                    <!-- Right: Navigation -->
                    <nav v-if="canLogin && !$page.props.auth.user" class="flex items-center space-x-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="text-sm font-medium text-gray-700 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400"
                        >
                            Create Account
                        </Link>
                    </nav>
                    <nav v-else-if="$page.props.auth.user">
                        <Link
                            :href="route('admin.dashboard')"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-700"
                        >
                            Go to Dashboard
                        </Link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                <!-- Left Column: Welcome Message -->
                <div class="flex flex-col justify-center space-y-8">
                    <!-- Welcome Message -->
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                            Welcome to Student Management System
                        </h2>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                            Student Management System for Georgia Job Challenge Academy
                        </p>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">
                            A comprehensive solution for managing student records, courses, grades, attendance, and academic operations.
                        </p>
                    </div>

                    <!-- Features -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Student Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Comprehensive student records and enrollment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Course Tracking</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Manage courses, schedules, and curriculum</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Grade Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Track assessments and academic performance</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Attendance Tracking</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Monitor and report student attendance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Login Form -->
                <div v-if="canLogin && !$page.props.auth.user" class="flex items-center justify-center lg:justify-end">
                    <div class="w-full max-w-md">
                        <div class="overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-6">
                                <h3 class="text-2xl font-bold text-white">
                                    Sign In
                                </h3>
                                <p class="mt-1 text-sm text-primary-100">
                                    Access your account to continue
                                </p>
                            </div>

                            <form @submit.prevent="submit" class="space-y-6 px-8 py-8">
                                <!-- Status Message -->
                                <div v-if="status" class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                    <p class="text-sm text-green-800 dark:text-green-200">
                                        {{ status }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <InputLabel for="email" value="Email Address" />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Enter your email"
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <!-- Password -->
                                <div>
                                    <InputLabel for="password" value="Password" />
                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Enter your password"
                                    />
                                    <InputError class="mt-2" :message="form.errors.password" />
                                </div>

                                <!-- Remember Me & Forgot Password -->
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.remember" name="remember" />
                                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                            Remember me
                                        </span>
                                    </label>

                                    <Link
                                        :href="route('password.request')"
                                        class="text-sm text-primary-600 underline hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                    >
                                        Forgot password?
                                    </Link>
                                </div>

                                <!-- Submit Button -->
                                <div>
                                    <PrimaryButton
                                        class="w-full justify-center"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Signing In...' : 'Sign In' }}
                                    </PrimaryButton>
                                </div>

                                <!-- Register Link -->
                                <div v-if="canRegister" class="text-center">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Don't have an account?
                                        <Link
                                            :href="route('register')"
                                            class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                        >
                                            Create one here
                                        </Link>
                                    </p>
                                </div>
                            </form>
                        </div>

                        <!-- Additional Info -->
                        <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Secure login powered by Student Management System
                        </p>
                    </div>
                </div>

                <!-- Logged In State -->
                <div v-else-if="$page.props.auth.user" class="flex items-center justify-center lg:justify-end">
                    <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-xl dark:bg-gray-800">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                            <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Welcome back!
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            You're already signed in as <strong>{{ $page.props.auth.user.name }}</strong>
                        </p>
                        <Link
                            :href="route('admin.dashboard')"
                            class="mt-6 inline-flex items-center rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white transition hover:bg-primary-700"
                        >
                            Go to Dashboard
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-gray-200 bg-white/50 py-8 backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                    &copy; {{ new Date().getFullYear() }} Brian K. Rich. All rights reserved.
                </p>
                <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-500">
                    Powered by Student Management System
                </p>
            </div>
        </footer>
    </div>
</template>
