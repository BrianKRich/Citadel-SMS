<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    academicYears: { type: Array, default: () => [] },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const selectedCohort = ref('alpha');

const form = useForm({
    academic_year_id: '',
    class_number:     '',
    ngb_number:       '',
    status:           'forming',
    alpha_start_date: '',
    alpha_end_date:   '',
    bravo_start_date: '',
    bravo_end_date:   '',
});

function submit() {
    form.post(route('admin.classes.store'));
}
</script>

<template>
    <Head title="Add Class" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Class</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Class Management', href: route('admin.class-layout.index') },
                    { label: 'Class Setup', href: route('admin.classes.index') },
                    { label: 'Add Class' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="New Class" description="Create a new class cohort." />

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <!-- Academic Year -->
                        <div>
                            <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Academic Year <span class="text-red-500">*</span>
                            </label>
                            <select id="academic_year_id" v-model="form.academic_year_id" required :class="inputClass">
                                <option value="">Select academic year...</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                    {{ year.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.academic_year_id }}</p>
                        </div>

                        <!-- Class Number -->
                        <div>
                            <label for="class_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Class Number <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="class_number"
                                v-model="form.class_number"
                                type="text"
                                placeholder="e.g. 42"
                                required
                                :class="inputClass"
                            />
                            <p v-if="form.errors.class_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.class_number }}</p>
                        </div>

                        <!-- NGB Number -->
                        <div>
                            <label for="ngb_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                NGB Number <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="ngb_number"
                                v-model="form.ngb_number"
                                type="text"
                                placeholder="e.g. NGB-2025-042"
                                required
                                :class="inputClass"
                            />
                            <p v-if="form.errors.ngb_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.ngb_number }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>
                            <select id="status" v-model="form.status" :class="inputClass">
                                <option value="forming">Forming</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                        </div>

                        <!-- Cohort Dates -->
                        <div class="rounded-md border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                            <!-- Cohort Selector -->
                            <div>
                                <label for="cohort_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                                <select id="cohort_select" v-model="selectedCohort" :class="inputClass">
                                    <option value="alpha">Cohort Alpha</option>
                                    <option value="bravo">Cohort Bravo</option>
                                </select>
                            </div>

                            <!-- Alpha Date Fields -->
                            <div v-if="selectedCohort === 'alpha'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="alpha_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                    <input
                                        id="alpha_start_date"
                                        v-model="form.alpha_start_date"
                                        type="date"
                                        :class="inputClass"
                                    />
                                    <p v-if="form.errors.alpha_start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.alpha_start_date }}</p>
                                </div>
                                <div>
                                    <label for="alpha_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                                    <input
                                        id="alpha_end_date"
                                        v-model="form.alpha_end_date"
                                        type="date"
                                        :class="inputClass"
                                    />
                                    <p v-if="form.errors.alpha_end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.alpha_end_date }}</p>
                                </div>
                            </div>

                            <!-- Bravo Date Fields -->
                            <div v-if="selectedCohort === 'bravo'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="bravo_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                    <input
                                        id="bravo_start_date"
                                        v-model="form.bravo_start_date"
                                        type="date"
                                        :class="inputClass"
                                    />
                                    <p v-if="form.errors.bravo_start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.bravo_start_date }}</p>
                                </div>
                                <div>
                                    <label for="bravo_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                                    <input
                                        id="bravo_end_date"
                                        v-model="form.bravo_end_date"
                                        type="date"
                                        :class="inputClass"
                                    />
                                    <p v-if="form.errors.bravo_end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.bravo_end_date }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.classes.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
