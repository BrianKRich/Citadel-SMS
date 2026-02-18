<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    employee: Object,
    departments: Array,
});

const form = useForm({
    first_name: props.employee.first_name ?? '',
    last_name: props.employee.last_name ?? '',
    email: props.employee.email ?? '',
    department_id: props.employee.department_id ?? '',
    role_id: props.employee.role_id ?? '',
    phone_area_code: '',
    phone: '',
    date_of_birth: props.employee.date_of_birth ?? '',
    hire_date: props.employee.hire_date ?? '',
    qualifications: props.employee.qualifications ?? '',
    photo: null,
    status: props.employee.status ?? 'active',
});

const selectedDepartment = computed(() =>
    props.departments.find(d => d.id == form.department_id)
);

const availableRoles = computed(() =>
    selectedDepartment.value?.roles ?? []
);

watch(() => form.department_id, (newVal, oldVal) => {
    if (oldVal !== '') {
        form.role_id = '';
    }
});

function submit() {
    form.patch(route('admin.employees.update', props.employee.id), { forceFormData: true });
}
</script>

<template>
    <Head :title="`Edit — ${employee.first_name} ${employee.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Employee
            </h2>
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
                    <PageHeader
                        :title="`${employee.first_name} ${employee.last_name}`"
                        :description="`Employee ID: ${employee.employee_id}`"
                    />

                    <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-8">

                        <!-- Section 1: Personal Info -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.first_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.first_name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.last_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.last_name }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Date of Birth
                                    </label>
                                    <input
                                        type="date"
                                        v-model="form.date_of_birth"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.date_of_birth }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Contact Information
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        v-model="form.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Phone Number
                                    </label>
                                    <div class="mt-1 grid grid-cols-4 gap-2">
                                        <div class="col-span-1">
                                            <input
                                                type="text"
                                                v-model="form.phone_area_code"
                                                placeholder="555"
                                                maxlength="3"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                        <div class="col-span-3">
                                            <input
                                                type="text"
                                                v-model="form.phone"
                                                placeholder="1234567"
                                                maxlength="7"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <p v-if="form.errors.phone_area_code" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.phone_area_code }}
                                    </p>
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Employment -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Employment Details
                            </h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Department <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.department_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    >
                                        <option value="">— Select Department —</option>
                                        <option v-for="d in departments" :key="d.id" :value="d.id">
                                            {{ d.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.department_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Role <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.role_id"
                                        :disabled="!form.department_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                        required
                                    >
                                        <option value="">— Select Role —</option>
                                        <option v-for="r in availableRoles" :key="r.id" :value="r.id">
                                            {{ r.name }}
                                        </option>
                                    </select>
                                    <p v-if="!form.department_id" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Select a department first
                                    </p>
                                    <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.role_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Hire Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="form.hire_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    />
                                    <p v-if="form.errors.hire_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.hire_date }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        required
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="on_leave">On Leave</option>
                                    </select>
                                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.status }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Photo & Qualifications -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Photo &amp; Qualifications
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Profile Photo
                                    </label>

                                    <!-- Current photo preview -->
                                    <div v-if="employee.photo" class="mt-2 flex items-center gap-4">
                                        <img
                                            :src="`/storage/${employee.photo}`"
                                            :alt="`${employee.first_name} ${employee.last_name}`"
                                            class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                        />
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Current photo. Upload a new one to replace it.
                                        </span>
                                    </div>

                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="form.photo = $event.target.files[0]"
                                        class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200"
                                    />
                                    <p v-if="form.errors.photo" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.photo }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Qualifications
                                    </label>
                                    <textarea
                                        v-model="form.qualifications"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        placeholder="Education, certifications, skills..."
                                    ></textarea>
                                    <p v-if="form.errors.qualifications" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.qualifications }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('admin.employees.show', employee.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                &larr; Back to Profile
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Update Employee' }}
                            </PrimaryButton>
                        </div>

                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
