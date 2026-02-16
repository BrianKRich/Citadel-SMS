<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    courses: Array,
});

const form = useForm({
    course_id: '',
    name: '',
    weight: '',
    description: '',
});

function submit() {
    form.post(route('admin.assessment-categories.store'));
}
</script>

<template>
    <Head title="Create Assessment Category" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Create Assessment Category
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            title="Add Assessment Category"
                            description="Define a grading category for assessments"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Course -->
                        <div>
                            <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Course <span class="text-gray-400 dark:text-gray-500 font-normal">(leave blank for Global)</span>
                            </label>
                            <select
                                id="course_id"
                                v-model="form.course_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Global (applies to all courses) —</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">
                                    {{ course.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.course_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.course_id }}
                            </p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Homework, Quizzes, Exams"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Weight -->
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Weight (0–1) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="weight"
                                v-model="form.weight"
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                placeholder="e.g. 0.25 for 25%"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Enter a decimal between 0 and 1 (e.g. 0.30 = 30%)
                            </p>
                            <p v-if="form.errors.weight" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.weight }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Optional description of this category"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Category' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.assessment-categories.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.assessment-categories.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            ← Back to Assessment Categories
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
