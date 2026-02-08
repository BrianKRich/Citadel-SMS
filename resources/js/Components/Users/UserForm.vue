<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['submit']);

const roles = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Administrator' },
];
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
        <!-- Name -->
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

        <!-- Role -->
        <div>
            <InputLabel for="role" value="Role" />
            <select
                id="role"
                v-model="form.role"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
            >
                <option value="" disabled>Select a role</option>
                <option v-for="role in roles" :key="role.value" :value="role.value">
                    {{ role.label }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.role" />
        </div>

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
