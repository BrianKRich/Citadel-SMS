<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    record:    { type: Object, required: true },
    courses:   { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
});

const form = useForm({
    employee_id:        props.record.employee_id,
    training_course_id: props.record.training_course_id,
    date_completed:     props.record.date_completed?.substring(0, 10) ?? '',
    trainer_name:       props.record.trainer_name,
    notes:              props.record.notes ?? '',
});

watch(() => form.training_course_id, (courseId) => {
    const course = props.courses.find(c => c.id === courseId);
    if (course) {
        form.trainer_name = course.trainer;
    }
});

function submit() {
    form.put(route('admin.training-records.update', props.record.id));
}
</script>

<template>
    <Head title="Edit Training Record" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Training Record
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Staff Training', href: route('admin.training-records.index') },
                    { label: 'Edit Record' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="Edit Training Record" />

                    <form @submit.prevent="submit" class="mt-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Employee <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.employee_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="" disabled>Select an employee</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.last_name }}, {{ emp.first_name }} ({{ emp.employee_id }})
                                </option>
                            </select>
                            <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.employee_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Training Course <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.training_course_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="" disabled>Select a course</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
                            </select>
                            <p v-if="form.errors.training_course_id" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.training_course_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date Completed <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.date_completed"
                                type="date"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p v-if="form.errors.date_completed" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.date_completed }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Trainer Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.trainer_name"
                                type="text"
                                maxlength="255"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p v-if="form.errors.trainer_name" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.trainer_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.notes }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('admin.training-records.show', record.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving…' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
