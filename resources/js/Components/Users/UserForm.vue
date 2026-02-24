<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['submit']);

const systemRoles = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Administrator' },
    { value: 'site_admin', label: 'Site Admin' },
];

const filteredRoles = computed(() => {
    if (!props.form.department_id) return [];
    const dept = props.departments.find(d => d.id == props.form.department_id);
    return dept ? dept.roles : [];
});

function onDepartmentChange() {
    props.form.role_id = '';
}
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">

        <!-- Create-only: First & Last Name -->
        <template v-if="!isEditing">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="first_name" value="First Name" />
                    <TextInput
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div>
                    <InputLabel for="last_name" value="Last Name" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>
        </template>

        <!-- Edit-only: single Name field -->
        <template v-else>
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
        </template>

        <!-- Email -->
        <div>
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full"
                required
            />
            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- System Role (admin/user) -->
        <div>
            <InputLabel for="role" value="System Role" />
            <select
                id="role"
                v-model="form.role"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
            >
                <option value="" disabled>Select a role</option>
                <option v-for="r in systemRoles" :key="r.value" :value="r.value">
                    {{ r.label }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.role" />
        </div>

        <!-- Create-only: Department & Employee Role -->
        <template v-if="!isEditing">
            <div>
                <InputLabel for="department_id" value="Department" />
                <select
                    id="department_id"
                    v-model="form.department_id"
                    @change="onDepartmentChange"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                >
                    <option value="" disabled>Select a department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                        {{ dept.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.department_id" />
            </div>

            <div>
                <InputLabel for="role_id" value="Job Title / Role" />
                <select
                    id="role_id"
                    v-model="form.role_id"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                    :disabled="!form.department_id"
                >
                    <option value="" disabled>{{ form.department_id ? 'Select a role' : 'Select a department first' }}</option>
                    <option v-for="r in filteredRoles" :key="r.id" :value="r.id">
                        {{ r.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.role_id" />
            </div>
        </template>

        <!-- Password -->
        <div>
            <InputLabel for="password" :value="isEditing ? 'New Password (leave blank to keep current)' : 'Password'" />
            <TextInput
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                :required="!isEditing"
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Password Confirmation -->
        <div>
            <InputLabel for="password_confirmation" value="Confirm Password" />
            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                :required="!isEditing && form.password"
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <PrimaryButton :disabled="form.processing">
                {{ form.processing ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
            </PrimaryButton>
        </div>
    </form>
</template>
