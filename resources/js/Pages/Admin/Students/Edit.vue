<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    customFields: { type: Array, default: () => [] },
});

const form = useForm({
    _method: 'patch',
    first_name: props.student.first_name ?? '',
    last_name: props.student.last_name ?? '',
    middle_name: props.student.middle_name ?? '',
    date_of_birth: props.student.date_of_birth ? props.student.date_of_birth.substring(0, 10) : '',
    gender: props.student.gender ?? '',
    ssn: '',
    email: props.student.email ?? '',
    phone_area_code: '',
    phone: '',
    address: props.student.address ?? '',
    city: props.student.city ?? '',
    state: props.student.state ?? '',
    postal_code: props.student.postal_code ?? '',
    emergency_contact_name: props.student.emergency_contact_name ?? '',
    emergency_phone_area_code: '',
    emergency_contact_phone: props.student.emergency_contact_phone ?? '',
    enrollment_date: props.student.enrollment_date ? props.student.enrollment_date.substring(0, 10) : '',
    status: props.student.status ?? '',
    photo: null,
    notes: props.student.notes ?? '',
    custom_field_values: {},
});

function submit() {
    form.post(route('admin.students.update', props.student.id), { forceFormData: true });
}
</script>

<template>
    <Head :title="`Edit Student: ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Student: {{ student.first_name }} {{ student.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Students', href: route('admin.students.index') },
                    { label: `${student.first_name} ${student.last_name}`, href: route('admin.students.show', student.id) },
                    { label: 'Edit' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <div class="mb-6">
                        <PageHeader
                            :title="`Edit Student: ${student.first_name} ${student.last_name}`"
                            :description="`Student ID: ${student.student_id}`"
                        />
                    </div>

                    <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">

                        <!-- Section 1: Personal Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- First Name -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input id="first_name" v-model="form.first_name" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.first_name }}</p>
                                </div>
                                <!-- Last Name -->
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input id="last_name" v-model="form.last_name" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.last_name }}</p>
                                </div>
                                <!-- Middle Name -->
                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Middle Name</label>
                                    <input id="middle_name" v-model="form.middle_name" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.middle_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.middle_name }}</p>
                                </div>
                                <!-- Date of Birth -->
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>
                                    <input id="date_of_birth" v-model="form.date_of_birth" type="date"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.date_of_birth }}</p>
                                </div>
                                <!-- Gender -->
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Gender <span class="text-red-500">*</span>
                                    </label>
                                    <select id="gender" v-model="form.gender"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <option value="">Select gender...</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.gender }}</p>
                                </div>
                                <!-- SSN -->
                                <div>
                                    <label for="ssn" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Social Security Number</label>
                                    <input id="ssn" v-model="form.ssn" type="text" maxlength="11" placeholder="###-##-####"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm font-mono" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Leave blank to keep current SSN{{ student.masked_ssn ? ` (${student.masked_ssn})` : '' }}.
                                    </p>
                                    <p v-if="form.errors.ssn" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.ssn }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Contact Information
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input id="email" v-model="form.email" type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                                </div>
                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <input id="phone_area_code" v-model="form.phone_area_code" type="text" maxlength="3" placeholder="555"
                                            class="mt-1 col-span-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                        <input id="phone" v-model="form.phone" type="text" maxlength="7" placeholder="5551234"
                                            class="mt-1 col-span-3 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Area code and 7-digit number</p>
                                    <p v-if="form.errors.phone_area_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_area_code }}</p>
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                                </div>
                                <!-- Address (full width) -->
                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                    <textarea id="address" v-model="form.address" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                                </div>
                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                                    <input id="city" v-model="form.city" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.city" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.city }}</p>
                                </div>
                                <!-- State + Postal Code -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                                        <input id="state" v-model="form.state" type="text" maxlength="2" placeholder="GA"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                        <p v-if="form.errors.state" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.state }}</p>
                                    </div>
                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                                        <input id="postal_code" v-model="form.postal_code" type="text" maxlength="10"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                        <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.postal_code }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Emergency Contact -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Emergency Contact
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Emergency Contact Name -->
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Name</label>
                                    <input id="emergency_contact_name" v-model="form.emergency_contact_name" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.emergency_contact_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.emergency_contact_name }}</p>
                                </div>
                                <!-- Emergency Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Phone</label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <input id="emergency_phone_area_code" v-model="form.emergency_phone_area_code" type="text" maxlength="3" placeholder="555"
                                            class="mt-1 col-span-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                        <input id="emergency_contact_phone" v-model="form.emergency_contact_phone" type="text" maxlength="7" placeholder="5551234"
                                            class="mt-1 col-span-3 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Area code and 7-digit number</p>
                                    <p v-if="form.errors.emergency_phone_area_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.emergency_phone_area_code }}</p>
                                    <p v-if="form.errors.emergency_contact_phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.emergency_contact_phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Academic Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Academic Information
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Enrollment Date -->
                                <div>
                                    <label for="enrollment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Enrollment Date <span class="text-red-500">*</span>
                                    </label>
                                    <input id="enrollment_date" v-model="form.enrollment_date" type="date"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p v-if="form.errors.enrollment_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.enrollment_date }}</p>
                                </div>
                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select id="status" v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <option value="">Select status...</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="graduated">Graduated</option>
                                        <option value="withdrawn">Withdrawn</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                                </div>
                                <!-- Photo -->
                                <div class="sm:col-span-2">
                                    <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Photo</label>
                                    <div v-if="student.photo" class="mb-2">
                                        <img :src="'/storage/' + student.photo" class="h-20 w-20 rounded-lg object-cover"
                                            :alt="`${student.first_name} ${student.last_name}`" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Current photo — upload a new one to replace it</p>
                                    </div>
                                    <input id="photo" type="file" accept="image/*" @change="form.photo = $event.target.files[0]"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. Accepted formats: JPG, PNG, GIF, WebP.</p>
                                    <p v-if="form.errors.photo" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.photo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Notes -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Notes
                            </h3>
                            <textarea id="notes" v-model="form.notes" rows="3"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.notes }}</p>
                        </div>

                        <!-- Custom Fields -->
                        <CustomFieldsSection
                            v-if="customFields.length"
                            :fields="customFields"
                            v-model="form.custom_field_values"
                        />

                        <!-- Submit -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Student' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.students.show', student.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <Link
                            :href="route('admin.students.show', student.id)"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        >
                            ← Back to Student
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
