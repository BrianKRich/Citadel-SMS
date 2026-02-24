<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    courses:             { type: Array, default: () => [] },
    employees:           { type: Array, default: () => [] },
    preselectedEmployee: { type: Array, default: () => [] },
});

const form = useForm({
    employee_ids:       [...props.preselectedEmployee],
    training_course_id: '',
    date_completed:     '',
    trainer_name:       '',
    notes:              '',
});

// Employee search / multi-select
const employeeSearch = ref('');

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase();
    if (!q) return props.employees;
    return props.employees.filter(emp =>
        emp.first_name.toLowerCase().includes(q) ||
        emp.last_name.toLowerCase().includes(q) ||
        emp.employee_id.toLowerCase().includes(q)
    );
});

function toggleEmployee(id) {
    const idx = form.employee_ids.indexOf(id);
    if (idx === -1) {
        form.employee_ids.push(id);
    } else {
        form.employee_ids.splice(idx, 1);
    }
}

function selectAllFiltered() {
    filteredEmployees.value.forEach(emp => {
        if (!form.employee_ids.includes(emp.id)) {
            form.employee_ids.push(emp.id);
        }
    });
}

function clearSelection() {
    form.employee_ids = [];
}

watch(() => form.training_course_id, (courseId) => {
    const course = props.courses.find(c => c.id === courseId);
    if (course) {
        form.trainer_name = course.trainer;
    }
});

function submit() {
    form.post(route('admin.training-records.store'));
}
</script>

<template>
    <Head title="Log Training Completion" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Log Training Completion
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Staff Training', href: route('admin.training-records.index') },
                    { label: 'Log Completion' },
                ]" />

                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="Log Training Completion" />

                    <form @submit.prevent="submit" class="mt-6 space-y-5">

                        <!-- Employee multi-select with search -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Employees <span class="text-red-500">*</span>
                                </label>
                                <span
                                    v-if="form.employee_ids.length"
                                    class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900 px-2.5 py-0.5 text-xs font-medium text-primary-800 dark:text-primary-200"
                                >
                                    {{ form.employee_ids.length }} selected
                                </span>
                            </div>

                            <!-- Search input -->
                            <div class="relative mb-1">
                                <input
                                    v-model="employeeSearch"
                                    type="text"
                                    placeholder="Search by name or ID…"
                                    class="w-full rounded-t-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>

                            <!-- Select all / clear row -->
                            <div class="flex items-center gap-3 px-3 py-1.5 bg-gray-50 dark:bg-gray-800 border-x border-gray-300 dark:border-gray-600 text-xs">
                                <button
                                    type="button"
                                    @click="selectAllFiltered"
                                    class="text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    Select all{{ employeeSearch ? ' matching' : '' }}
                                    ({{ filteredEmployees.length }})
                                </button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button
                                    type="button"
                                    @click="clearSelection"
                                    class="text-gray-500 dark:text-gray-400 hover:underline"
                                >Clear</button>
                            </div>

                            <!-- Scrollable checklist -->
                            <div class="max-h-52 overflow-y-auto border border-t-0 border-gray-300 dark:border-gray-600 rounded-b-md divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-700">
                                <label
                                    v-for="emp in filteredEmployees"
                                    :key="emp.id"
                                    class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600"
                                    :class="form.employee_ids.includes(emp.id) ? 'bg-primary-50 dark:bg-primary-900/30' : ''"
                                >
                                    <input
                                        type="checkbox"
                                        :value="emp.id"
                                        :checked="form.employee_ids.includes(emp.id)"
                                        @change="toggleEmployee(emp.id)"
                                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ emp.last_name }}, {{ emp.first_name }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ emp.employee_id }})</span>
                                    </span>
                                </label>
                                <div
                                    v-if="!filteredEmployees.length"
                                    class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400"
                                >
                                    No employees match "{{ employeeSearch }}"
                                </div>
                            </div>

                            <p v-if="form.errors.employee_ids" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.employee_ids }}</p>
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
                                :href="route('admin.training-records.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.employee_ids.length"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                            >
                                <template v-if="form.processing">Saving…</template>
                                <template v-else-if="form.employee_ids.length > 1">
                                    Log {{ form.employee_ids.length }} Completions
                                </template>
                                <template v-else>Log Completion</template>
                            </button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
