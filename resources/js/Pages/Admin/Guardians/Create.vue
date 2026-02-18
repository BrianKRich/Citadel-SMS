<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    students: Array,
});

const form = useForm({
    first_name: '',
    last_name: '',
    relationship: '',
    email: '',
    phone_area_code: '',
    phone: '',
    address: '',
    occupation: '',
    students: [],
});

function toggleStudent(student) {
    const idx = form.students.findIndex(s => s.id === student.id);
    if (idx >= 0) {
        form.students.splice(idx, 1);
    } else {
        form.students.push({ id: student.id, is_primary: false });
    }
}

function isSelected(studentId) {
    return form.students.some(s => s.id === studentId);
}

function getStudentEntry(studentId) {
    return form.students.find(s => s.id === studentId);
}

function submit() {
    form.post(route('admin.guardians.store'));
}
</script>

<template>
    <Head title="Add Guardian" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Guardian</h2>
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
                    <PageHeader title="Add Guardian" />

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Section 1: Personal Info -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.first_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.last_name }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Relationship <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.relationship"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="" disabled>Select relationship</option>
                                        <option value="mother">Mother</option>
                                        <option value="father">Father</option>
                                        <option value="guardian">Guardian</option>
                                        <option value="grandparent">Grandparent</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <p v-if="form.errors.relationship" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.relationship }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                                </div>

                                <!-- Phone: area code + number side-by-side -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <div class="grid grid-cols-4 gap-2 mt-1">
                                        <div class="col-span-1">
                                            <input
                                                v-model="form.phone_area_code"
                                                type="text"
                                                maxlength="3"
                                                placeholder="Area"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                        <div class="col-span-3">
                                            <input
                                                v-model="form.phone"
                                                type="text"
                                                maxlength="7"
                                                placeholder="Number"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <p v-if="form.errors.phone_area_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_area_code }}</p>
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                    <textarea
                                        v-model="form.address"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Occupation</label>
                                    <input
                                        v-model="form.occupation"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.occupation" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.occupation }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Linked Students -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Linked Students</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Linked Students</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3">
                                    <div v-for="student in students" :key="student.id" class="flex items-center justify-between">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="isSelected(student.id)"
                                                @change="toggleStudent(student)"
                                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500"
                                            />
                                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ student.first_name }} {{ student.last_name }} ({{ student.student_id }})
                                            </span>
                                        </label>
                                        <label v-if="isSelected(student.id)" class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <input
                                                type="checkbox"
                                                v-model="getStudentEntry(student.id).is_primary"
                                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500"
                                            />
                                            Primary
                                        </label>
                                    </div>
                                    <p v-if="!students.length" class="text-sm text-gray-500 dark:text-gray-400 py-2 text-center">
                                        No active students found.
                                    </p>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Select students and optionally mark one as primary guardian.
                                </p>
                                <p v-if="form.errors.students" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.students }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('admin.guardians.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                &larr; Back to Guardians
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Add Guardian' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
