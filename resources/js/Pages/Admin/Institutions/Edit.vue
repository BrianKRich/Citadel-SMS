<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    institution: { type: Object, required: true },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

const form = useForm({
    name:           props.institution.name ?? '',
    type:           props.institution.type ?? 'technical_college',
    address:        props.institution.address ?? '',
    phone:          props.institution.phone ?? '',
    contact_person: props.institution.contact_person ?? '',
});

function submit() {
    form.patch(route('admin.institutions.update', props.institution.id));
}
</script>

<template>
    <Head :title="`Edit ${institution.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Institution
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Institutions', href: route('admin.institutions.index') },
                    { label: institution.name },
                    { label: 'Edit' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader :title="`Edit: ${institution.name}`" description="Update institution details." />

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                :class="inputClass"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select id="type" v-model="form.type" required :class="inputClass">
                                <option value="technical_college">Technical College</option>
                                <option value="university">University</option>
                            </select>
                            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                :class="inputClass"
                            ></textarea>
                            <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                :class="inputClass"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label for="contact_person" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person</label>
                            <input
                                id="contact_person"
                                v-model="form.contact_person"
                                type="text"
                                :class="inputClass"
                            />
                            <p v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.contact_person }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.institutions.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
