<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    customFields: { type: Array, default: () => [] },
});

const form = useForm({
    course_code: '',
    name: '',
    description: '',
    credits: '',
    department: '',
    level: '',
    is_active: true,
    custom_field_values: {},
});

function submit() {
    form.post(route('admin.courses.store'));
}
</script>

<template>
    <Head title="Create Course" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Create Course</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="New Course" description="Add a new course to the catalog." />

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Course Code -->
                        <div>
                            <label for="course_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Course Code <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="course_code"
                                v-model="form.course_code"
                                type="text"
                                placeholder="e.g. MATH-101"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Must be unique across all courses</p>
                            <p v-if="form.errors.course_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.course_code }}</p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Course Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Introduction to Algebra"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
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
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</p>
                        </div>

                        <!-- Credits -->
                        <div>
                            <label for="credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Credits
                            </label>
                            <input
                                id="credits"
                                v-model="form.credits"
                                type="number"
                                step="0.5"
                                min="0"
                                max="999"
                                placeholder="e.g. 3.0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.credits" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.credits }}</p>
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Department
                            </label>
                            <input
                                id="department"
                                v-model="form.department"
                                type="text"
                                placeholder="e.g. Mathematics"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Department or subject area</p>
                            <p v-if="form.errors.department" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.department }}</p>
                        </div>

                        <!-- Level -->
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Level
                            </label>
                            <input
                                id="level"
                                v-model="form.level"
                                type="text"
                                placeholder="e.g. Beginner, Intermediate, Advanced"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.level" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.level }}</p>
                        </div>

                        <!-- Is Active -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-primary-600 shadow-sm focus:ring-primary-500"
                                />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Active course</span>
                            </label>
                            <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.is_active }}</p>
                        </div>

                        <!-- Custom Fields -->
                        <CustomFieldsSection
                            v-if="customFields.length"
                            :fields="customFields"
                            v-model="form.custom_field_values"
                        />

                        <!-- Button Row -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Course' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.courses.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                        </div>
                    </form>
                </Card>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <Link
                        :href="route('admin.courses.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >← Back to Courses</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
