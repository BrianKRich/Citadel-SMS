<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    classes: Array,
    categories: Array,
});

const form = useForm({
    class_id: '',
    assessment_category_id: '',
    name: '',
    max_score: 100,
    due_date: '',
    weight: '',
    is_extra_credit: false,
    status: 'draft',
});

function submit() {
    form.post(route('admin.assessments.store'));
}
</script>

<template>
    <Head title="Create Assessment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Create Assessment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Grade Management', href: route('admin.grades.index') },
                    { label: 'Add Assessment' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            title="Add Assessment"
                            description="Create a new assessment for a class"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Class -->
                        <div>
                            <label for="class_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="class_id"
                                v-model="form.class_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">Select a class...</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    {{ cls.course?.name || 'N/A' }} — {{ cls.section_name }}
                                </option>
                            </select>
                            <p v-if="form.errors.class_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.class_id }}
                            </p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="assessment_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Assessment Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="assessment_category_id"
                                v-model="form.assessment_category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">Select a category...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}{{ cat.course ? ` (${cat.course.name})` : ' (Global)' }}
                                </option>
                            </select>
                            <p v-if="form.errors.assessment_category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.assessment_category_id }}
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
                                placeholder="e.g. Midterm Exam, Homework 1"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Max Score -->
                        <div>
                            <label for="max_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Score <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="max_score"
                                v-model="form.max_score"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.max_score" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.max_score }}
                            </p>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Due Date
                            </label>
                            <input
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.due_date }}
                            </p>
                        </div>

                        <!-- Weight (optional override) -->
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Weight Override <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                            </label>
                            <input
                                id="weight"
                                v-model="form.weight"
                                type="number"
                                step="0.01"
                                min="0"
                                max="1"
                                placeholder="Leave blank to use category weight"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.weight" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.weight }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Extra Credit -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_extra_credit"
                                v-model="form.is_extra_credit"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                            />
                            <label for="is_extra_credit" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Extra Credit
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Assessment' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.assessments.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.assessments.index')"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            ← Back to Assessments
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
