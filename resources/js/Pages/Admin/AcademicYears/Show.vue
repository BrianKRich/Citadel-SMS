<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    academicYear: Object,
});

function setCurrent() {
    router.post(route('admin.academic-years.set-current', props.academicYear.id));
}

function destroyYear() {
    if (confirm('Delete this academic year?')) {
        router.delete(route('admin.academic-years.destroy', props.academicYear.id));
    }
}

function setCurrentTerm(term) {
    router.post(route('admin.academic-years.terms.set-current', [props.academicYear.id, term.id]));
}

function destroyTerm(term) {
    if (confirm(`Delete term "${term.name}"?`)) {
        router.delete(route('admin.academic-years.terms.destroy', [props.academicYear.id, term.id]));
    }
}

const showAddTerm = ref(false);
const termForm = useForm({ name: '', start_date: '', end_date: '', is_current: false });

function submitTerm() {
    termForm.post(route('admin.academic-years.terms.store', props.academicYear.id), {
        onSuccess: () => {
            showAddTerm.value = false;
            termForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="`Academic Year: ${academicYear.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Academic Year Details
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Academic Years', href: route('admin.academic-years.index') },
                    { label: academicYear.name },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Academic Year Info Card -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <PageHeader :title="academicYear.name" />
                                <span
                                    v-if="academicYear.is_current"
                                    class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 font-medium"
                                >
                                    Current
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-3 flex-wrap flex-shrink-0">
                            <button
                                v-if="!academicYear.is_current"
                                @click="setCurrent"
                                class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                Set as Current
                            </button>
                            <Link
                                :href="route('admin.academic-years.edit', academicYear.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroyYear"
                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(academicYear.start_date).toLocaleDateString() }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ new Date(academicYear.end_date).toLocaleDateString() }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Terms</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ academicYear.terms.length }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Classes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ academicYear.classes.length }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Terms Card -->
                <Card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Terms</h3>
                        <button
                            type="button"
                            @click="showAddTerm = !showAddTerm"
                            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
                        >
                            {{ showAddTerm ? 'Cancel' : '+ Add Term' }}
                        </button>
                    </div>

                    <!-- Add Term Form -->
                    <div
                        v-if="showAddTerm"
                        class="mb-4 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800"
                    >
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Add New Term</h4>
                        <form @submit.prevent="submitTerm" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Term Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="termForm.name"
                                    type="text"
                                    placeholder="e.g. Fall 2024"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                />
                                <p v-if="termForm.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ termForm.errors.name }}
                                </p>
                            </div>

                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="termForm.is_current"
                                        type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Set as current term
                                    </span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="termForm.start_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                />
                                <p v-if="termForm.errors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ termForm.errors.start_date }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    End Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="termForm.end_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                />
                                <p v-if="termForm.errors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ termForm.errors.end_date }}
                                </p>
                            </div>

                            <div class="sm:col-span-2 flex gap-3 items-center">
                                <PrimaryButton type="submit" :disabled="termForm.processing">
                                    {{ termForm.processing ? 'Adding...' : 'Add Term' }}
                                </PrimaryButton>
                                <button
                                    type="button"
                                    @click="showAddTerm = false"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Terms Table -->
                    <div v-if="academicYear.terms.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Current</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="term in academicYear.terms" :key="term.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ term.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(term.start_date).toLocaleDateString() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(term.end_date).toLocaleDateString() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            v-if="term.is_current"
                                            class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 font-medium"
                                        >
                                            Current
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex gap-3">
                                            <button
                                                v-if="!term.is_current"
                                                @click="setCurrentTerm(term)"
                                                class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium"
                                            >
                                                Set Current
                                            </button>
                                            <button
                                                @click="destroyTerm(term)"
                                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No terms defined for this academic year.
                        <button
                            type="button"
                            @click="showAddTerm = true"
                            class="ml-1 text-primary-600 dark:text-primary-400 hover:underline"
                        >
                            Add a term now
                        </button>
                    </div>
                </Card>

                <!-- Classes Card -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Classes</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Classes scheduled for this academic year</p>
                    </div>

                    <div v-if="academicYear.classes.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Course Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Section</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="cls in academicYear.classes" :key="cls.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900 dark:text-gray-100">
                                        {{ cls.course?.course_code || '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ cls.course?.name || '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.section_name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No classes are associated with this academic year.
                    </div>
                </Card>

                <!-- Back Link -->
                <div class="px-4 sm:px-0">
                    <Link
                        :href="route('admin.academic-years.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Academic Years
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
